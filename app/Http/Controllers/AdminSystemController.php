<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CauHinhGoiY;
use App\Models\MoHinhGoiY;
use App\Models\SinhVien;
use App\Models\HocPhan;
use App\Models\NganhHoc;
use App\Models\MuonTra;
use App\Models\TaiLieu;
use App\Services\NlpTokenizerService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdminSystemController extends Controller
{
    public function dashboard()
    {
        $studentCount = SinhVien::count();
        $courseCount = HocPhan::count();
        $majorCount = NganhHoc::count();
        $borrowCount = MuonTra::count();
        
        $activeModel = MoHinhGoiY::where('LaHienHanh', 1)->first();
        $totalModels = MoHinhGoiY::count();

        return view('admin.system.dashboard', compact(
            'studentCount', 'courseCount', 'majorCount', 'borrowCount', 
            'activeModel', 'totalModels'
        ));
    }

        public function importDataView()
        {
            return view('admin.system.import');
        }

        public function processImportData(Request $request)
{
    // 1. Kiểm tra dữ liệu đầu vào
    $request->validate([
        'data_type'   => 'required',
        'file_import' => 'required|file|mimes:csv,txt,xlsx,xls|max:5000',
    ], [
        'data_type.required'   => 'Vui lòng chọn loại dữ liệu đào tạo cần nhập!',
        'file_import.required' => 'Vui lòng chọn tệp dữ liệu!',
        'file_import.mimes'    => 'Hệ thống chỉ hỗ trợ định dạng .csv, .xlsx, .xls!',
        'file_import.max'      => 'Dung lượng tệp không được vượt quá 5MB!',
    ]);

    $type = strtolower(trim($request->input('data_type')));

    try {
        // 2. Đọc dữ liệu từ tệp
        $file = $request->file('file_import');
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (empty($rows) || count($rows) < 2) {
            return redirect()->back()
                ->with('error', 'Tệp dữ liệu rỗng hoặc chỉ có dòng tiêu đề!');
        }

        $headerRow = array_shift($rows);

        // 3. Chuẩn hóa tiêu đề cột
        $normalize = function ($value) {
            if ($value === null) {
                return '';
            }

            $value = mb_strtolower(trim((string) $value));
            $value = Str::ascii($value);

            return preg_replace('/[^a-z0-9]/', '', $value);
        };

        // Tìm vị trí cột theo tên tiêu đề
        $findColIdx = function ($keywords) use ($headerRow, $normalize) {
            foreach ($headerRow as $index => $header) {
                $normalizedHeader = $normalize($header);

                foreach ($keywords as $keyword) {
                    if (
                        $normalizedHeader !== '' &&
                        str_contains($normalizedHeader, $keyword)
                    ) {
                        return $index;
                    }
                }
            }

            return null;
        };

        // Lấy dữ liệu tại một ô, tránh lỗi khi thiếu cột
        $getCell = function ($data, $index) {
            if ($index === null || !isset($data[$index])) {
                return null;
            }

            $value = trim((string) $data[$index]);

            return $value === '' ? null : $value;
        };

        // 4. Xác định vị trí các cột
        $maHpIdx      = $findColIdx(['mahocphan', 'mamonhoc', 'mahp', 'mamon']);
        $tenHpIdx     = $findColIdx(['tenhocphan', 'tenmonhoc', 'tenhp', 'tenmon']);
        $stcIdx       = $findColIdx(['sotinchi', 'stc', 'sotc', 'tinchi']);
        $moTaIdx      = $findColIdx(['mota', 'ghichu', 'tomtat']);

        $maNganhIdx   = $findColIdx(['manganh', 'machuyennganh', 'manch']);
        $tenNganhIdx  = $findColIdx(['tennganh', 'tenchuyennganh', 'tench']);

        $mssvIdx      = $findColIdx(['mssv', 'masinhvien', 'masv', 'mathe']);
        $hoTenIdx     = $findColIdx(['hoten', 'tensinhvien', 'tensv']);
        $ngaySinhIdx  = $findColIdx(['ngaysinh', 'ns']);
        $gioiTinhIdx  = $findColIdx(['gioitinh', 'phai']);
        $sdtIdx       = $findColIdx(['sodienthoai', 'sdt', 'dienthoai', 'phone']);
        $hanTheIdx    = $findColIdx(['hanthe', 'ngayhethan']);
        $maTkIdx      = $findColIdx(['mataikhoan', 'matk', 'taikhoan']);

        $loaiTlIdx    = $findColIdx(['loaitailieu', 'loaitl']);
        $ndDeCuongIdx = $findColIdx(['noidungdecuong', 'decuong']);
        $trangThaiIdx = $findColIdx(['trangthai', 'trangthaihoc']);

        // 5. Kiểm tra cấu trúc tiêu đề theo từng loại dữ liệu
        $structureError = null;

        if (in_array($type, ['nganh_hoc', 'nganhhoc'])) {
            if ($maNganhIdx === null || $tenNganhIdx === null) {
                $structureError = 'Tệp ngành học phải có cột Mã ngành và Tên ngành.';
            }
        } elseif (in_array($type, ['hoc_phan', 'hocphan'])) {
            if ($maHpIdx === null || $tenHpIdx === null) {
                $structureError = 'Tệp học phần phải có cột Mã học phần và Tên học phần.';
            }
        } elseif (in_array($type, ['de_cuong_hoc_phan', 'decuonghocphan'])) {
            if ($maHpIdx === null || ($ndDeCuongIdx === null && $moTaIdx === null)) {
                $structureError = 'Tệp đề cương phải có cột Mã học phần và Nội dung đề cương.';
            }
        } elseif (in_array($type, ['chuong_trinh_dao_tao', 'chuongtrinhdaotao'])) {
            if ($maNganhIdx === null || $maHpIdx === null) {
                $structureError = 'Tệp chương trình đào tạo phải có cột Mã ngành và Mã học phần.';
            }
        } elseif (in_array($type, ['sinh_vien', 'sinhvien'])) {
            if ($mssvIdx === null || $hoTenIdx === null) {
                $structureError = 'Tệp sinh viên phải có cột MSSV và Họ tên.';
            }
        } elseif (in_array($type, ['sinh_vien_hoc_phan', 'sinhvienhocphan'])) {
            if ($mssvIdx === null || $maHpIdx === null) {
                $structureError = 'Tệp sinh viên - học phần phải có cột MSSV và Mã học phần.';
            }
        } else {
            $structureError = 'Loại dữ liệu nhập không được hỗ trợ.';
        }

        if ($structureError) {
            return redirect()->back()->with('error', $structureError);
        }

        // 6. Hàm kiểm tra ngày tháng
        // Hỗ trợ d/m/Y, Y-m-d và ngày Excel dạng số
        $parseDate = function ($value) {
            if ($value === null || trim((string) $value) === '') {
                return null;
            }

            // Ngày tháng dạng số của Excel
            if (is_numeric($value) && (float) $value > 0) {
                try {
                    return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject(
                        $value
                    )->format('Y-m-d');
                } catch (\Throwable $e) {
                    return false;
                }
            }

            $value = trim((string) $value);

            foreach (['d/m/Y', 'Y-m-d', 'd-m-Y'] as $format) {
                $date = \DateTime::createFromFormat('!' . $format, $value);

                if ($date && $date->format($format) === $value) {
                    return $date->format('Y-m-d');
                }
            }

            return false;
        };

        // 7. Khởi tạo biến thống kê
        $countSuccess = 0;
        $countSkipped = 0;
        $errors = [];
        $processedKeys = [];

        DB::beginTransaction();

        // 8. Kiểm tra và nhập từng dòng dữ liệu
        foreach ($rows as $rowIndex => $data) {
            if (empty(array_filter($data, function ($value) {
                return $value !== null && trim((string) $value) !== '';
            }))) {
                continue;
            }

            $lineNum = $rowIndex + 2;

            /*
             * NGÀNH HỌC
             */
            if (in_array($type, ['nganh_hoc', 'nganhhoc'])) {
                $maNganh = $getCell($data, $maNganhIdx);
                $tenNganh = $getCell($data, $tenNganhIdx);

                if (empty($maNganh) || empty($tenNganh)) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Thiếu mã ngành hoặc tên ngành.";
                    continue;
                }

                if (NganhHoc::where('MaNganh', $maNganh)->exists()) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Mã ngành [{$maNganh}] đã tồn tại trong hệ thống.";
                    continue;
                }

                if (in_array($maNganh, $processedKeys, true)) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Mã ngành [{$maNganh}] bị trùng trong tệp.";
                    continue;
                }

                NganhHoc::create([
                    'MaNganh' => $maNganh,
                    'TenNganh' => $tenNganh,
                ]);

                $processedKeys[] = $maNganh;
                $countSuccess++;
            }

            /*
             * HỌC PHẦN
             */
            elseif (in_array($type, ['hoc_phan', 'hocphan'])) {
                $maHocPhan = $getCell($data, $maHpIdx);
                $tenHocPhan = $getCell($data, $tenHpIdx);
                $stcValue = $getCell($data, $stcIdx);
                $moTa = $getCell($data, $moTaIdx);

                if (empty($maHocPhan) || empty($tenHocPhan)) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Thiếu mã học phần hoặc tên học phần.";
                    continue;
                }

                if (HocPhan::where('MaHocPhan', $maHocPhan)->exists()) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Mã học phần [{$maHocPhan}] đã tồn tại trong hệ thống.";
                    continue;
                }

                if (in_array($maHocPhan, $processedKeys, true)) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Mã học phần [{$maHocPhan}] bị trùng trong tệp.";
                    continue;
                }

                if ($stcValue !== null &&
                    (!is_numeric($stcValue) || (int) $stcValue != $stcValue || (int) $stcValue < 0)) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Số tín chỉ không hợp lệ.";
                    continue;
                }

                HocPhan::create([
                    'MaHocPhan' => $maHocPhan,
                    'TenHocPhan' => $tenHocPhan,
                    'SoTinChi' => $stcValue !== null ? (int) $stcValue : null,
                    'MoTa' => $moTa,
                ]);

                $processedKeys[] = $maHocPhan;
                $countSuccess++;
            }

            /*
             * ĐỀ CƯƠNG HỌC PHẦN
             */
            elseif (in_array($type, ['de_cuong_hoc_phan', 'decuonghocphan'])) {
                $maHocPhan = $getCell($data, $maHpIdx);
                $noiDung = $getCell($data, $ndDeCuongIdx ?? $moTaIdx);
                $loaiTaiLieu = $getCell($data, $loaiTlIdx) ?? 'Giáo trình chính';

                if (empty($maHocPhan) || empty($noiDung)) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Thiếu mã học phần hoặc nội dung đề cương.";
                    continue;
                }

                if (!HocPhan::where('MaHocPhan', $maHocPhan)->exists()) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Học phần [{$maHocPhan}] chưa tồn tại.";
                    continue;
                }

                if (DB::table('decuonghocphan')->where('MaHocPhan', $maHocPhan)->exists()) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Đề cương của học phần [{$maHocPhan}] đã tồn tại.";
                    continue;
                }

                if (in_array($maHocPhan, $processedKeys, true)) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Đề cương học phần [{$maHocPhan}] bị trùng trong tệp.";
                    continue;
                }

                DB::table('decuonghocphan')->insert([
                    'MaHocPhan' => $maHocPhan,
                    'NoiDungDeCuong' => $noiDung,
                    'LoaiTaiLieu' => $loaiTaiLieu,
                ]);

                $processedKeys[] = $maHocPhan;
                $countSuccess++;
            }

            /*
             * CHƯƠNG TRÌNH ĐÀO TẠO
             */
            elseif (in_array($type, ['chuong_trinh_dao_tao', 'chuongtrinhdaotao'])) {
                $maNganh = $getCell($data, $maNganhIdx);
                $maHocPhan = $getCell($data, $maHpIdx);

                if (empty($maNganh) || empty($maHocPhan)) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Thiếu mã ngành hoặc mã học phần.";
                    continue;
                }

                if (!NganhHoc::where('MaNganh', $maNganh)->exists()) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Mã ngành [{$maNganh}] chưa tồn tại.";
                    continue;
                }

                if (!HocPhan::where('MaHocPhan', $maHocPhan)->exists()) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Mã học phần [{$maHocPhan}] chưa tồn tại.";
                    continue;
                }

                $key = $maNganh . '|' . $maHocPhan;

                if (DB::table('chuongtrinhdaotao')
                    ->where('MaNganh', $maNganh)
                    ->where('MaHocPhan', $maHocPhan)
                    ->exists()) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Quan hệ ngành [{$maNganh}] - học phần [{$maHocPhan}] đã tồn tại.";
                    continue;
                }

                if (in_array($key, $processedKeys, true)) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Cặp ngành - học phần bị trùng trong tệp.";
                    continue;
                }

                DB::table('chuongtrinhdaotao')->insert([
                    'MaNganh' => $maNganh,
                    'MaHocPhan' => $maHocPhan,
                ]);

                $processedKeys[] = $key;
                $countSuccess++;
            }

            /*
             * HỒ SƠ SINH VIÊN
             */
            elseif (in_array($type, ['sinh_vien', 'sinhvien'])) {
                $mssv = $getCell($data, $mssvIdx);
                $hoTen = $getCell($data, $hoTenIdx);
                $maNganh = $getCell($data, $maNganhIdx);

                if (empty($mssv) || empty($hoTen)) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Thiếu MSSV hoặc họ tên sinh viên.";
                    continue;
                }

                if (SinhVien::where('Mssv', $mssv)->exists()) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: MSSV [{$mssv}] đã tồn tại trong hệ thống.";
                    continue;
                }

                if (in_array($mssv, $processedKeys, true)) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: MSSV [{$mssv}] bị trùng trong tệp.";
                    continue;
                }

                if ($maNganh && !NganhHoc::where('MaNganh', $maNganh)->exists()) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Mã ngành [{$maNganh}] không tồn tại.";
                    continue;
                }

                $ngaySinhValue = $getCell($data, $ngaySinhIdx);
                $hanTheValue = $getCell($data, $hanTheIdx);

                $ngaySinh = $parseDate($ngaySinhValue);
                $hanThe = $parseDate($hanTheValue);

                if ($ngaySinhValue !== null && $ngaySinh === false) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Ngày sinh không đúng định dạng.";
                    continue;
                }

                if ($hanTheValue !== null && $hanThe === false) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Hạn thẻ không đúng định dạng.";
                    continue;
                }

                $gioiTinh = $getCell($data, $gioiTinhIdx) ?? 'Nam';

                if (!in_array($gioiTinh, ['Nam', 'Nữ', 'Khác'], true)) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Giới tính không hợp lệ.";
                    continue;
                }

                $sdt = $getCell($data, $sdtIdx);
                if ($sdt !== null && !preg_match('/^[0-9]{9,11}$/', $sdt)) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Số điện thoại không hợp lệ.";
                    continue;
                }

                $maTk = $getCell($data, $maTkIdx);

                // Không cho phép một tài khoản gắn với nhiều sinh viên
                if ($maTk && SinhVien::where('MaTaiKhoan', $maTk)->exists()) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Tài khoản [{$maTk}] đã được gắn với sinh viên khác.";
                    continue;
                }

                SinhVien::create([
                    'Mssv' => $mssv,
                    'HoTen' => $hoTen,
                    'NgaySinh' => $ngaySinh,
                    'GioiTinh' => $gioiTinh,
                    'SoDienThoai' => $sdt,
                    'HanThe' => $hanThe ?? '2027-06-30',
                    'MaNganh' => $maNganh,
                    'MaTaiKhoan' => $maTk,
                ]);

                $processedKeys[] = $mssv;
                $countSuccess++;
            }

            /*
             * SINH VIÊN - HỌC PHẦN
             */
            elseif (in_array($type, ['sinh_vien_hoc_phan', 'sinhvienhocphan'])) {
                $mssv = $getCell($data, $mssvIdx);
                $maHocPhan = $getCell($data, $maHpIdx);

                if (empty($mssv) || empty($maHocPhan)) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Thiếu MSSV hoặc mã học phần.";
                    continue;
                }

                if (!SinhVien::where('Mssv', $mssv)->exists()) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Sinh viên [{$mssv}] chưa tồn tại.";
                    continue;
                }

                if (!HocPhan::where('MaHocPhan', $maHocPhan)->exists()) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Học phần [{$maHocPhan}] chưa tồn tại.";
                    continue;
                }

                $key = $mssv . '|' . $maHocPhan;

                if (DB::table('sinhvienhocphan')
                    ->where('Mssv', $mssv)
                    ->where('MaHocPhan', $maHocPhan)
                    ->exists()) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Sinh viên [{$mssv}] đã có học phần [{$maHocPhan}].";
                    continue;
                }

                if (in_array($key, $processedKeys, true)) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Cặp sinh viên - học phần bị trùng trong tệp.";
                    continue;
                }

                $trangThai = $getCell($data, $trangThaiIdx) ?? 'Đang học';

                if (!in_array($trangThai, ['Đang học', 'Đã hoàn thành'], true)) {
                    $countSkipped++;
                    $errors[] = "Dòng {$lineNum}: Trạng thái học phần không hợp lệ.";
                    continue;
                }

                DB::table('sinhvienhocphan')->insert([
                    'Mssv' => $mssv,
                    'MaHocPhan' => $maHocPhan,
                    'TrangThai' => $trangThai,
                ]);

                $processedKeys[] = $key;
                $countSuccess++;
            }
        }

        // 9. Xử lý kết quả nhập
        if ($countSuccess === 0) {
            DB::rollBack();

            $errorDetails = implode(' | ', array_slice($errors, 0, 8));

            return redirect()->back()->with(
                'error',
                'Không có bản ghi hợp lệ để nhập. ' .
                ($errorDetails ?: 'Vui lòng kiểm tra lại dữ liệu trong tệp.')
            );
        }

        // Có dữ liệu hợp lệ thì lưu các dòng thành công
        DB::commit();

        // Xóa cache gợi ý để dữ liệu mới được cập nhật
        try {
            Cache::forget('app_latest_config');
        } catch (\Throwable $e) {
            // Không làm gián đoạn kết quả nhập nếu cache gặp lỗi
        }

        $message = "Nhập dữ liệu hoàn tất! Thành công: {$countSuccess} bản ghi.";

        if ($countSkipped > 0) {
            $errorDetails = implode(' | ', array_slice($errors, 0, 5));

            $message .= " Bỏ qua {$countSkipped} dòng lỗi hoặc trùng. ";
            $message .= "Chi tiết: {$errorDetails}";
        }

        return redirect()->back()->with('success', $message);

    } catch (\Throwable $e) {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }

        return redirect()->back()->with(
            'error',
            'Lỗi xử lý tệp: ' . $e->getMessage()
        );
    }
}

    public function configView()
    {
        $config = CauHinhGoiY::orderBy('created_at', 'desc')->first() 
            ?? CauHinhGoiY::orderBy('MaCauHinh', 'desc')->first() 
            ?? new CauHinhGoiY(['TopN' => 10, 'NguongCosine' => 0.15]);

        $activeModel = MoHinhGoiY::where('LaHienHanh', 1)->first();

        return view('admin.system.config', compact('config', 'activeModel'));
    }

    public function saveConfig(Request $request)
    {
        $request->validate([
            'TopN'         => 'required|integer|min:1',
            'NguongCosine' => 'required|numeric|between:0,1',
        ]);

        DB::beginTransaction();
        try {
            // Luôn sinh MÃ CẤU HÌNH MỚI và TẠO BẢN GHI MỚI để lưu lịch sử
            $maCauHinhMoi = 'CH_' . time();

            CauHinhGoiY::create([
                'MaCauHinh'  => $maCauHinhMoi,
                'MaTaiKhoan' => session('user_id'),
                'TopN'        => $request->TopN,
                'NguongCosine'=> $request->NguongCosine,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            // Thao tác xóa Cache bọc an toàn
            try {
                Cache::forget('app_latest_config');
            } catch (\Throwable $e) {
                // Bỏ qua nếu bảng Cache chưa sẵn sàng
            }

            DB::commit();

            return redirect()->back()->with('success', "Đã tạo cấu hình tham số mới [{$maCauHinhMoi}] thành công!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Lưu cấu hình thất bại: ' . $e->getMessage());
        }
    }
    public function retrainModel()
    {
        MoHinhGoiY::where('LaHienHanh', 1)->update(['LaHienHanh' => 0]);

        MoHinhGoiY::create([
            'MaMoHinh'    => 'MH_' . time(),
            'MaTaiKhoan'  => session('user_id'),
            'MaCauHinh'   => 'CH_001',
            'ThoiDiemTao' => now(),
            'DuongDanTep'  => 'models/tfidf_vectorizer_v' . time() . '.pkl',
            'LaHienHanh'  => 1,
        ]);

        return redirect()->back()->with('success', 'Mô hình gợi ý đã được cập nhật thành công!');
    }

}