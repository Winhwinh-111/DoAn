<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaiLieu;
use App\Models\MuonTra;
use App\Models\PhanHoi;
use App\Models\LichSuTimKiem;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class AdminLibraryController extends Controller
{
    public function dashboard()
    {
        // 1. Dữ liệu tổng quan
        $totalBooks = TaiLieu::count();
        $totalBorrowing = MuonTra::where('TrangThai', 'DangMuon')->count(); // Đang cho mượn
        $recentBorrows = MuonTra::with(['taiLieu', 'sinhVien'])->orderBy('NgayMuon', 'desc')->limit(5)->get();

        // 2. Dữ liệu thống kê 
        $totalFeedback = PhanHoi::count();
        $goodFeedback = PhanHoi::where('MucDanhGia', 'PhuHop')->count();
        $badFeedback = PhanHoi::where('MucDanhGia', 'KhongPhuHop')->count();

        $topKeywords = LichSuTimKiem::select('TuKhoa', DB::raw('count(*) as count'))
            ->groupBy('TuKhoa')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Thống kê mượn trả 
        $totalBorrowedHistory = MuonTra::count(); // Tổng số lượng đã mượn
        $totalReturned = MuonTra::where('TrangThai', 'DaTra')->count(); // Tổng số lượng đã trả

        return view('admin.library.dashboard', compact(
            'totalBooks', 'totalBorrowing', 'totalFeedback', 'recentBorrows',
            'goodFeedback', 'badFeedback', 'topKeywords',
            'totalBorrowedHistory', 'totalReturned'
        ));
    }

    public function documentsIndex(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $query = TaiLieu::query();

        if (!empty($keyword)) {
            $query->where('NhanDe', 'LIKE', "%{$keyword}%")
                  ->orWhere('TacGia', 'LIKE', "%{$keyword}%")
                  ->orWhere('MaTaiLieu', 'LIKE', "%{$keyword}%");
        }

        $documents = $query->orderBy('MaTaiLieu', 'desc')->paginate(10);
        return view('admin.library.documents', compact('documents', 'keyword'));
    }

    public function documentsStore(Request $request)
    {
        $request->validate([
            'MaTaiLieu' => 'required|unique:TaiLieu,MaTaiLieu',
            'NhanDe'     => 'required',
        ]);

        TaiLieu::create([
            'MaTaiLieu'        => $request->MaTaiLieu,
            'NhanDe'            => $request->NhanDe,
            'TacGia'            => $request->TacGia,
            'NhaXuatBan'       => $request->NhaXuatBan,
            'NamXuatBan'       => $request->NamXuatBan ?? date('Y'),
            'NgonNgu'           => $request->NgonNgu ?? 'Tiếng Việt',
            'ISBN'               => $request->ISBN,
            'ThuatNguChuDe'   => $request->ThuatNguChuDe,
            'MoTa'              => $request->MoTa,
            'NoiLuuGiu'        => $request->NoiLuuGiu ?? 'Kho tầng 1',
            'TrangThaiSuDung' => $request->TrangThaiSuDung ?? 'Co_San',
            'AnhBia'            => $request->AnhBia,
        ]);

        return redirect()->back()->with('success', 'Thêm mới tài liệu thành công!');
    }

    public function documentsUpdate(Request $request, $id)
    {
        $doc = TaiLieu::findOrFail($id);
        $doc->update($request->all());
        return redirect()->back()->with('success', 'Cập nhật thông tin tài liệu thành công!');
    }

    public function documentsDelete($id)
    {
        $doc = TaiLieu::findOrFail($id);
        $doc->delete();
        return redirect()->back()->with('success', 'Xóa tài liệu thành công!');
    }

    // Nhập tệp danh mục TaiLieu 
    
    
public function documentsImport(Request $request)
{
    // 1. Kiểm tra file tải lên
    $request->validate([
        'file_import' => 'required|file|mimes:csv,txt,xlsx,xls|max:5000',
    ], [
        'file_import.required' => 'Vui lòng chọn tệp dữ liệu!',
        'file_import.file'     => 'Tệp tải lên không hợp lệ!',
        'file_import.mimes'    => 'Chỉ hỗ trợ CSV, TXT, XLSX và XLS!',
        'file_import.max'      => 'Dung lượng file tối đa là 5MB!',
    ]);

    try {
        // 2. Đọc file Excel
        $file = $request->file('file_import');

        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();

        $rows = $sheet->toArray(null, true, true, false);

        if (empty($rows)) {
            return redirect()->back()
                ->with('error', 'File dữ liệu đang trống!');
        }

        // 3. Chuẩn hóa tên cột
        $normalize = function ($value) {

            if ($value === null || is_array($value)) {
                return '';
            }

            $value = trim((string) $value);

            // Xóa BOM
            $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);

            // Chuyển về chữ thường
            $value = mb_strtolower($value, 'UTF-8');

            // Bỏ dấu tiếng Việt
            $value = \Illuminate\Support\Str::ascii($value);

            // Loại bỏ khoảng trắng và ký tự đặc biệt
            return preg_replace('/[^a-z0-9]/', '', $value);
        };

        // 4. Danh sách tên cột được chấp nhận
        $columnAliases = [
            'MaTaiLieu' => [
                'matailieu', 'matl', 'masach'
            ],

            'NhanDe' => [
                'nhande', 'tensach', 'tentailieu',
                'tieude', 'title'
            ],

            'TacGia' => [
                'tacgia', 'author'
            ],

            'NhaXuatBan' => [
                'nhaxuatban', 'nxb', 'publisher'
            ],

            'NamXuatBan' => [
                'namxuatban', 'namxb', 'nam',
                'publicationyear'
            ],

            'NgonNgu' => [
                'ngonngu', 'language'
            ],

            'ISBN' => [
                'isbn'
            ],

            'ThuatNguChuDe' => [
                'thuatnguchude', 'chude',
                'thuatngu', 'keywords'
            ],

            'MoTa' => [
                'mota', 'tomtat', 'description'
            ],

            'NoiLuuGiu' => [
                'noiluugiu', 'vitri', 'kho',
                'location'
            ],

            'TrangThaiSuDung' => [
                'trangthaisudung', 'trangthai',
                'status'
            ],

            'AnhBia' => [
                'anhbia', 'hinhbia', 'cover',
                'image', 'url'
            ],
        ];

        // 5. Tự động tìm dòng tiêu đề
        // Hỗ trợ file có dòng Column1, Column2 ở phía trên
        $headerIndex = null;
        $columnIndexes = [];

        foreach ($rows as $rowIndex => $row) {

            $normalizedHeaders = [];

            foreach ($row as $columnIndex => $header) {
                $normalizedHeaders[$columnIndex] = $normalize($header);
            }

            $tempIndexes = [];

            foreach ($columnAliases as $field => $aliases) {

                $tempIndexes[$field] = null;

                foreach ($normalizedHeaders as $columnIndex => $header) {

                    if (in_array($header, $aliases, true)) {
                        $tempIndexes[$field] = $columnIndex;
                        break;
                    }
                }
            }

            // Chỉ nhận dòng có đủ 2 cột bắt buộc
            if (
                $tempIndexes['MaTaiLieu'] !== null &&
                $tempIndexes['NhanDe'] !== null
            ) {
                $headerIndex = $rowIndex;
                $columnIndexes = $tempIndexes;
                break;
            }
        }

        if ($headerIndex === null) {
            return redirect()->back()->with(
                'error',
                'Không tìm thấy dòng tiêu đề chứa hai cột bắt buộc: MaTaiLieu và NhanDe!'
            );
        }

        // 6. Lấy dữ liệu phía dưới dòng tiêu đề
        $dataRows = array_slice($rows, $headerIndex + 1);

        // 7. Hàm lấy dữ liệu theo tên cột
        $getValue = function ($row, $field) use ($columnIndexes) {

            $index = $columnIndexes[$field] ?? null;

            if ($index === null || !array_key_exists($index, $row)) {
                return null;
            }

            $value = $row[$index];

            if ($value === null || is_array($value)) {
                return null;
            }

            $value = trim((string) $value);

            return $value === '' ? null : $value;
        };

        // 8. Hàm kiểm tra độ dài
        $checkLength = function ($value, $max, $label) {

            if (
                $value !== null &&
                mb_strlen($value, 'UTF-8') > $max
            ) {
                return "{$label} vượt quá {$max} ký tự";
            }

            return null;
        };

        // 9. Khởi tạo thống kê
        $countSuccess = 0;

        $skipReasons = [
            'missing_code'   => 0,
            'missing_title'  => 0,
            'duplicate_db'   => 0,
            'duplicate_file' => 0,
            'invalid_year'   => 0,
            'invalid_length' => 0,
            'database_error' => 0,
        ];

        $errorDetails = [];

        // Lưu mã đã gặp trong file
        $seenCodes = [];

        // 10. Xử lý từng dòng
        foreach ($dataRows as $index => $row) {

            $excelRow = $headerIndex + $index + 2;

            // Bỏ qua dòng trống
            $hasData = false;

            foreach ($row as $cell) {
                if (
                    $cell !== null &&
                    trim((string) $cell) !== ''
                ) {
                    $hasData = true;
                    break;
                }
            }

            if (!$hasData) {
                continue;
            }

            // Lấy mã và nhan đề
            $maTaiLieu = $getValue($row, 'MaTaiLieu');
            $nhanDe = $getValue($row, 'NhanDe');

            // 10.1. Kiểm tra mã tài liệu
            if ($maTaiLieu === null) {
                $skipReasons['missing_code']++;
                $errorDetails[] =
                    "Dòng {$excelRow}: Thiếu mã tài liệu.";
                continue;
            }

            if ($nhanDe === null) {
                $skipReasons['missing_title']++;
                $errorDetails[] =
                    "Dòng {$excelRow}: Thiếu nhan đề tài liệu.";
                continue;
            }

            // 10.2. Không tự động cắt mã tài liệu
            $lengthError = $checkLength(
                $maTaiLieu,
                30,
                'Mã tài liệu'
            );

            if ($lengthError !== null) {
                $skipReasons['invalid_length']++;
                $errorDetails[] =
                    "Dòng {$excelRow}: {$lengthError}.";
                continue;
            }

            // 10.3. Kiểm tra độ dài nhan đề
            $lengthError = $checkLength(
                $nhanDe,
                300,
                'Nhan đề'
            );

            if ($lengthError !== null) {
                $skipReasons['invalid_length']++;
                $errorDetails[] =
                    "Dòng {$excelRow}: {$lengthError}.";
                continue;
            }

            // 10.4. Kiểm tra mã trùng trong file
            $codeKey = mb_strtolower($maTaiLieu, 'UTF-8');

            if (isset($seenCodes[$codeKey])) {
                $skipReasons['duplicate_file']++;
                $errorDetails[] =
                    "Dòng {$excelRow}: Mã tài liệu {$maTaiLieu} bị trùng trong file.";
                continue;
            }

            // Đánh dấu mã đã xuất hiện trong file
            $seenCodes[$codeKey] = true;

            // 10.5. Kiểm tra mã trùng trong CSDL
            if (
                TaiLieu::where('MaTaiLieu', $maTaiLieu)->exists()
            ) {
                $skipReasons['duplicate_db']++;
                $errorDetails[] =
                    "Dòng {$excelRow}: Mã tài liệu {$maTaiLieu} đã tồn tại trong CSDL.";
                continue;
            }

            // 10.6. Lấy các trường dữ liệu
            $tacGia = $getValue($row, 'TacGia');

            $nhaXuatBan = $getValue($row, 'NhaXuatBan')
                ?? 'Chưa cập nhật';

            $namXbVal = $getValue($row, 'NamXuatBan');

            $ngonNgu = $getValue($row, 'NgonNgu')
                ?? 'Tiếng Việt';

            $isbn = $getValue($row, 'ISBN');

            $chuDe = $getValue($row, 'ThuatNguChuDe');

            $moTa = $getValue($row, 'MoTa');

            $noiLuuGiu = $getValue($row, 'NoiLuuGiu')
                ?? 'Kho tầng 1';

            $trangThai = $getValue($row, 'TrangThaiSuDung')
                ?? 'Co_San';

            $anhBia = $getValue($row, 'AnhBia');

            // 10.7. Kiểm tra năm xuất bản
            $namXuatBan = null;

            if ($namXbVal !== null) {

                if (
                    !preg_match('/^\d{1,4}$/', $namXbVal) ||
                    (int) $namXbVal < 1000 ||
                    (int) $namXbVal > (int) date('Y')
                ) {
                    $skipReasons['invalid_year']++;

                    $errorDetails[] =
                        "Dòng {$excelRow}: Năm xuất bản không hợp lệ ({$namXbVal}).";

                    continue;
                }

                $namXuatBan = (int) $namXbVal;
            }

            // 10.8. Kiểm tra độ dài các trường
            $lengthChecks = [
                [$tacGia, 250, 'Tác giả'],
                [$nhaXuatBan, 150, 'Nhà xuất bản'],
                [$ngonNgu, 30, 'Ngôn ngữ'],
                [$isbn, 30, 'ISBN'],
                [$noiLuuGiu, 150, 'Nơi lưu giữ'],
                [$trangThai, 50, 'Trạng thái sử dụng'],
                [$anhBia, 255, 'Ảnh bìa'],
            ];

            $lengthError = null;

            foreach ($lengthChecks as [$value, $limit, $label]) {

                $lengthError = $checkLength(
                    $value,
                    $limit,
                    $label
                );

                if ($lengthError !== null) {
                    break;
                }
            }

            if ($lengthError !== null) {
                $skipReasons['invalid_length']++;

                $errorDetails[] =
                    "Dòng {$excelRow}: {$lengthError}.";

                continue;
            }

            // 10.9. Lưu từng dòng bằng transaction riêng
            try {

                DB::transaction(function () use (
                    $maTaiLieu,
                    $nhanDe,
                    $tacGia,
                    $nhaXuatBan,
                    $namXuatBan,
                    $ngonNgu,
                    $isbn,
                    $chuDe,
                    $moTa,
                    $noiLuuGiu,
                    $trangThai,
                    $anhBia
                ) {

                    TaiLieu::create([
                        'MaTaiLieu'        => $maTaiLieu,
                        'NhanDe'            => $nhanDe,
                        'TacGia'            => $tacGia,
                        'NhaXuatBan'       => $nhaXuatBan,
                        'NamXuatBan'       => $namXuatBan,
                        'NgonNgu'           => $ngonNgu,
                        'ISBN'               => $isbn,
                        'ThuatNguChuDe'   => $chuDe,
                        'MoTa'              => $moTa,
                        'NoiLuuGiu'        => $noiLuuGiu,
                        'TrangThaiSuDung' => $trangThai,
                        'AnhBia'            => $anhBia,
                    ]);

                });

                $countSuccess++;

            } catch (\Throwable $rowError) {

                report($rowError);

                // Kiểm tra lại để nhận diện lỗi trùng khóa
                if (
                    TaiLieu::where('MaTaiLieu', $maTaiLieu)->exists()
                ) {
                    $skipReasons['duplicate_db']++;

                    $errorDetails[] =
                        "Dòng {$excelRow}: Mã tài liệu {$maTaiLieu} đã tồn tại trong CSDL.";
                } else {
                    $skipReasons['database_error']++;

                    $errorDetails[] =
                        "Dòng {$excelRow}: Không thể lưu dữ liệu. Vui lòng kiểm tra cấu trúc CSDL hoặc log Laravel.";
                }
            }
        }

        // 11. Tổng hợp kết quả
        $countSkipped = array_sum($skipReasons);

        $message = "Import hoàn tất: Thành công {$countSuccess} tài liệu mới, "
            . "bỏ qua {$countSkipped} dòng không hợp lệ hoặc bị trùng.";

        return redirect()->back()
            ->with('success', $message)
            ->with('import_summary', $skipReasons)
            ->with('import_details', $errorDetails);

    } catch (\Throwable $e) {

        report($e);

        return redirect()->back()->with(
            'error',
            'Không thể xử lý file dữ liệu. Vui lòng kiểm tra định dạng file và cấu trúc bảng!'
        );
    }
}

    // Hiển thị danh sách mượn trả
    public function borrowIndex()
    {
        $borrowList = MuonTra::with(['taiLieu', 'sinhVien'])->orderBy('NgayMuon', 'desc')->paginate(10);
        return view('admin.library.borrow', compact('borrowList'));
    }

    // Tạo phiếu mượn tài liệu mới từ Modal
    public function borrowStore(Request $request)
    {
        $request->validate([
            'MSSV'        => 'required',
            'MaTaiLieu' => 'required',
        ]);

        MuonTra::create([
            'MaGiaoDich' => 'GD_' . time(),
            'MSSV'         => $request->MSSV,
            'MaTaiLieu'   => $request->MaTaiLieu,
            'NgayMuon'    => now(),
            'TrangThai'   => 'DangMuon',
            'GhiChu'      => $request->GhiChu
        ]);

        return redirect()->back()->with('success', 'Tạo phiếu mượn tài liệu thành công!');
    }

    // Cập nhật trạng thái trả sách
    public function borrowReturnStatus($id)
    {
        $borrow = MuonTra::findOrFail($id);
        $borrow->update([
            'NgayTra'   => now(),
            'TrangThai' => 'DaTra'
        ]);
        return redirect()->back()->with('success', 'Đã cập nhật trạng thái trả sách thành công!');
    }
}