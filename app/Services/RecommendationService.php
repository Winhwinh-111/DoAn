<?php

namespace App\Services;

use App\Models\SinhVien;
use App\Models\TaiLieu;
use App\Models\DeCuongHocPhan;
use App\Models\CauHinhGoiY;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    protected NlpTokenizerService $tokenizer;

    public function __construct(NlpTokenizerService $tokenizer)
    {
        $this->tokenizer = $tokenizer;
    }

    public function getRecommendationsForStudent(string $mssv, ?int $topN = null, ?float $minThreshold = null): array
    {
        // Đọc cấu hình an toàn, tự động loại bỏ Cache hỏng nếu phát hiện __PHP_Incomplete_Class
        $configData = null;
        try {
            $rawConfig = Cache::remember(
                'app_latest_config',
                86400,
                function () {
                    $item = CauHinhGoiY::orderByDesc('created_at')
                        ->orderByDesc('MaCauHinh')
                        ->first();
                    return $item ? $item->toArray() : null;
                }
            );

            // Kiểm tra nếu cache trả về incomplete class hoặc object cũ
            if (is_object($rawConfig)) {
                Cache::forget('app_latest_config');
                $item = CauHinhGoiY::orderByDesc('created_at')->orderByDesc('MaCauHinh')->first();
                $configData = $item ? $item->toArray() : null;
            } else {
                $configData = $rawConfig;
            }
        } catch (\Throwable $e) {
            $item = CauHinhGoiY::orderByDesc('created_at')
                ->orderByDesc('MaCauHinh')
                ->first();
            $configData = $item ? $item->toArray() : null;
        }

        // Lấy tham số động từ mảng cấu hình
        $topN = $topN ?? (isset($configData['TopN']) ? (int)$configData['TopN'] : 10);
        $minThreshold = $minThreshold ?? (isset($configData['NguongCosine']) ? (float)$configData['NguongCosine'] : 0.02);

        $student = SinhVien::with(['hocPhans' => function ($query) {
            $query->whereIn('TrangThai', ['DangHoc', 'DaHoanThanh']);
        }])->find($mssv);

        if (!$student) return [];

        $maHocPhans = $student->hocPhans->pluck('MaHocPhan')->toArray();

        // A. CHỈ GIỮ LẠI CÁC HỌC PHẦN HỢP LỆ (CÓ ĐỀ CƯƠNG)
        $validMaHocPhans = DB::table('DeCuongHocPhan')
            ->whereIn('MaHocPhan', $maHocPhans)
            ->distinct()
            ->pluck('MaHocPhan')
            ->toArray();

        // B. TUÂN THỦ DỰ PHÒNG NGOẠI LỆ (COLD-START)
        if (empty($validMaHocPhans)) {
            $maHocPhansCTDT = DB::table('ChuongTrinhDaoTao')
                ->where('MaNganh', $student->MaNganh)
                ->pluck('MaHocPhan')
                ->toArray();

            $validMaHocPhans = DB::table('DeCuongHocPhan')
                ->whereIn('MaHocPhan', $maHocPhansCTDT)
                ->distinct()
                ->pluck('MaHocPhan')
                ->toArray();

            // Nếu ngành không có học phần nào có đề cương hợp lệ -> Trả về rỗng
            if (empty($validMaHocPhans)) {
                return [];
            }
        }

        $maHocPhans = $validMaHocPhans;

        $nganh = DB::table('NganhHoc')->where('MaNganh', $student->MaNganh)->first();
        $tenNganh = $nganh ? $nganh->TenNganh : '';

        // Xây dựng tập văn bản Hồ sơ học tập
        $coursesText = [];
        $hocPhans = DB::table('HocPhan')
            ->whereIn('MaHocPhan', $maHocPhans)
            ->pluck('TenHocPhan', 'MaHocPhan');

        $deCuongs = DeCuongHocPhan::whereIn('MaHocPhan', $maHocPhans)->get();

        foreach ($hocPhans as $maHp => $tenHp) {
            $coursesText['COURSE_' . $maHp] = $tenNganh . ' ' . $tenHp . ' ';
        }

        foreach ($deCuongs as $dc) {
            $key = 'COURSE_' . $dc->MaHocPhan;
            if (!isset($coursesText[$key])) {
                $coursesText[$key] = $tenNganh . ' ';
            }
            $coursesText[$key] .= ' ' . $dc->noi_dung_de_cuong . ' ' . ($dc->loai_tai_lieu ?? '');
        }

        // C. LOẠI TÀI LIỆU ĐÃ MƯỢN: Chỉ lọc các tài liệu sinh viên "Đang mượn" chưa trả
        $borrowedDocIds = DB::table('MuonTra')
            ->where('MSSV', $mssv)
            ->where('TrangThai', 'DangMuon') 
            ->pluck('MaTaiLieu')
            ->toArray();

        $documents = TaiLieu::whereNotIn('MaTaiLieu', $borrowedDocIds)->get();
        if ($documents->isEmpty()) return [];

        $rawTextsToProcess = [];
        $profileKeys = [];
        $docIds = [];

        foreach ($coursesText as $key => $text) {
            $rawTextsToProcess[$key] = $text;
            $profileKeys[] = $key;
        }

        foreach ($documents as $doc) {
            $rawTextsToProcess[$doc->MaTaiLieu] = $doc->NhanDe . ' ' . $doc->ThuatNguChuDe . ' ' . $doc->MoTa;
            $docIds[] = $doc->MaTaiLieu;
        }

        $corpus = $this->tokenizer->tokenizeBatch($rawTextsToProcess);
        $tfidfMatrix = $this->calculateTFIDF($corpus);

        $scores = [];
        $reasons = [];

        foreach ($docIds as $docId) {
            $docVector = $tfidfMatrix[$docId] ?? [];
            $maxSimilarity = 0.0;
            $bestReason = '';

            foreach ($profileKeys as $pKey) {
                $profileVector = $tfidfMatrix[$pKey] ?? [];
                $similarity = $this->cosineSimilarity($profileVector, $docVector);

                if ($similarity > $maxSimilarity) {
                    $maxSimilarity = $similarity;
                    $bestReason = str_replace('COURSE_', '', $pKey);
                }
            }

            if ($maxSimilarity >= $minThreshold) {
                $scores[$docId] = $maxSimilarity;
                $reasons[$docId] = 'Học phần / Ngành: ' . $bestReason;
            }
        }

        // D. SẮP XẾP ƯU TIÊN MÃ TÀI LIỆU KHI BẰNG ĐIỂM
        uksort($scores, function ($a, $b) use ($scores) {
            if (abs($scores[$a] - $scores[$b]) < 0.00001) {
                return strcmp($a, $b); // Bằng điểm -> Sắp xếp A-Z theo mã tài liệu
            }
            return ($scores[$b] <=> $scores[$a]); // Khác điểm -> Sắp xếp giảm dần theo điểm
        });

        $topDocIds = array_slice(array_keys($scores), 0, $topN);

        $recommendedBooks = TaiLieu::whereIn('MaTaiLieu', $topDocIds)->get()->keyBy('MaTaiLieu');

        $result = [];
        foreach ($topDocIds as $id) {
            if (isset($recommendedBooks[$id])) {
                $book = $recommendedBooks[$id];
                $book->score = round($scores[$id], 4);
                $book->reason = $reasons[$id];
                $result[] = $book;
            }
        }

        return $result;
    }

    private function calculateTFIDF(array $corpus): array
    {
        $numDocs = count($corpus);
        $docFreq = [];

        foreach ($corpus as $words) {
            $uniqueWords = array_unique($words);
            foreach ($uniqueWords as $word) {
                $docFreq[$word] = ($docFreq[$word] ?? 0) + 1;
            }
        }

        $tfidfMatrix = [];

        foreach ($corpus as $docId => $words) {
            $wordCount = count($words);
            $tf = array_count_values($words);
            $vector = [];

            foreach ($tf as $word => $count) {
                $tfVal = $count / max(1, $wordCount);
                $idfVal = log(($numDocs + 1) / (($docFreq[$word] ?? 0) + 1)) + 1;
                $vector[$word] = $tfVal * $idfVal;
            }
            $tfidfMatrix[$docId] = $vector;
        }

        return $tfidfMatrix;
    }

    private function cosineSimilarity(array $vecA, array $vecB): float
    {
        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($vecA as $word => $weightA) {
            $normA += $weightA * $weightA;
            if (isset($vecB[$word])) {
                $dotProduct += $weightA * $vecB[$word];
            }
        }

        foreach ($vecB as $weightB) {
            $normB += $weightB * $weightB;
        }

        if ($normA == 0 || $normB == 0) return 0.0;

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }
}