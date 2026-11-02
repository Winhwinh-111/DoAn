<?php
/**
 * Cầu nối đánh giá: gọi trực tiếp RecommendationService của Laravel.
 * Đặt file này tại thư mục gốc dự án (cùng cấp artisan, .env, bootstrap/).
 */
declare(strict_types=1);

use App\Models\SinhVien;
use App\Models\CauHinhGoiY;
use App\Services\RecommendationService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

error_reporting(E_ALL);
ini_set('display_errors', '0');

try {
    if (!is_file(__DIR__ . '/vendor/autoload.php')) {
        throw new RuntimeException('Không tìm thấy vendor/autoload.php. Hãy chạy composer install trong dự án Laravel.');
    }
    if (!is_file(__DIR__ . '/.env')) {
        throw new RuntimeException('Không tìm thấy .env tại thư mục gốc Laravel.');
    }

    require __DIR__ . '/vendor/autoload.php';
    $app = require __DIR__ . '/bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();

    $limit = isset($argv[1]) ? max(0, (int) $argv[1]) : 0;

    // Đọc danh sách sinh viên trực tiếp từ CSDL đang được Laravel cấu hình.
    $studentQuery = SinhVien::query()->orderBy('MSSV');
    if ($limit > 0) {
        $studentQuery->limit($limit);
    }
    $studentIds = $studentQuery->pluck('MSSV')->all();

    /** @var RecommendationService $service */
    $service = app(RecommendationService::class);

    $results = [];
    $effectiveConfig = null;

    foreach ($studentIds as $mssv) {
        // GỌI ĐÚNG service mà HomeController đang sử dụng.
        $recommendations = $service->getRecommendationsForStudent((string) $mssv);

        // Service đã nạp cấu hình vào cache. Lấy cấu hình hiệu lực đó để ghi báo cáo.
        $cachedConfig = Cache::get('app_latest_config');
        if (is_array($cachedConfig)) {
            $effectiveConfig = [
                'MaCauHinh' => $cachedConfig['MaCauHinh'] ?? $cachedConfig['ma_cau_hinh'] ?? null,
                'TopN' => (int) ($cachedConfig['TopN'] ?? $cachedConfig['top_n'] ?? 10),
                'NguongCosine' => (float) ($cachedConfig['NguongCosine'] ?? $cachedConfig['nguong_cosine'] ?? 0.02),
                'source' => 'app_latest_config cache used by RecommendationService',
            ];
        }

        $student = SinhVien::with(['hocPhans' => function ($query) {
            $query->whereIn('TrangThai', ['DangHoc', 'DaHoanThanh']);
        }])->find((string) $mssv);

        if (!$student) {
            continue;
        }

        $enrolledCourseIds = $student->hocPhans->pluck('MaHocPhan')->map(fn ($v) => (string) $v)->all();

        // Sao chép chính xác quy tắc chọn học phần hợp lệ trong RecommendationService.
        $validCourseIds = DB::table('DeCuongHocPhan')
            ->whereIn('MaHocPhan', $enrolledCourseIds)
            ->distinct()
            ->pluck('MaHocPhan')
            ->map(fn ($v) => (string) $v)
            ->all();

        $profileSource = 'registered_courses';
        if (empty($validCourseIds)) {
            $programCourseIds = DB::table('ChuongTrinhDaoTao')
                ->where('MaNganh', $student->MaNganh)
                ->pluck('MaHocPhan')
                ->map(fn ($v) => (string) $v)
                ->all();

            $validCourseIds = DB::table('DeCuongHocPhan')
                ->whereIn('MaHocPhan', $programCourseIds)
                ->distinct()
                ->pluck('MaHocPhan')
                ->map(fn ($v) => (string) $v)
                ->all();

            $profileSource = 'major_fallback';
        }

        // Ground truth hiện có: tài liệu được liên kết trong đề cương của
        // các học phần thực sự được service chọn cho hồ sơ sinh viên.
        $relevantIds = [];
        if (!empty($validCourseIds)) {
            $relevantIds = DB::table('DeCuongHocPhan')
                ->whereIn('MaHocPhan', $validCourseIds)
                ->distinct()
                ->pluck('MaTaiLieu')
                ->map(fn ($v) => (string) $v)
                ->all();
        }

        // Cùng loại trừ tài liệu đang mượn như RecommendationService.
        $borrowedIds = DB::table('MuonTra')
            ->where('MSSV', (string) $mssv)
            ->where('TrangThai', 'DangMuon')
            ->pluck('MaTaiLieu')
            ->map(fn ($v) => (string) $v)
            ->all();

        $availableDocumentIds = DB::table('TaiLieu')
            ->whereNotIn('MaTaiLieu', $borrowedIds)
            ->pluck('MaTaiLieu')
            ->map(fn ($v) => (string) $v)
            ->all();

        $relevantIds = array_values(array_unique(array_intersect($relevantIds, $availableDocumentIds)));
        sort($relevantIds, SORT_STRING);

        $recommended = [];
        foreach ($recommendations as $item) {
            $recommended[] = [
                'id' => (string) $item->MaTaiLieu,
                'score' => isset($item->score) ? (float) $item->score : null,
                'reason' => $item->reason ?? null,
            ];
        }

        $results[] = [
            'MSSV' => (string) $mssv,
            'MaNganh' => (string) $student->MaNganh,
            'profile_source' => $profileSource,
            'profile_course_ids' => $validCourseIds,
            'recommended' => $recommended,
            'ground_truth' => $relevantIds,
        ];
    }

    // Nếu không có sinh viên để gọi service thì vẫn lấy cấu hình hiện tại từ DB
    // để báo cáo lý do/đầu vào minh bạch.
    if ($effectiveConfig === null) {
        $latest = CauHinhGoiY::orderByDesc('created_at')
            ->orderByDesc('MaCauHinh')
            ->first();
        if ($latest) {
            $effectiveConfig = [
                'MaCauHinh' => $latest->MaCauHinh,
                'TopN' => (int) $latest->TopN,
                'NguongCosine' => (float) $latest->NguongCosine,
                'source' => 'database fallback (no student call populated cache)',
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'generated_at' => now()->toIso8601String(),
        'config' => $effectiveConfig,
        'student_count' => count($results),
        'students' => $results,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

} catch (Throwable $e) {
    http_response_code(1);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'exception' => get_class($e),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit(1);
}
