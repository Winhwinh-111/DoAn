<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RecommendationService;
use App\Models\SinhVien;
use App\Models\TaiLieu;
use App\Models\LichSuTimKiem;
use App\Models\DanhDau;
use App\Models\PhanHoi;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    protected $recommendationService;

    public function __construct(RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    // Trang chủ, Gợi ý tài liệu và Tra cứu tài liệu
    public function index(Request $request)
    {
        $sinhVien = null;
        $recommendedBooks = [];
        $allBooks = [];
        $searchResults = null;
        $markedBookIds = [];
        $isSuggestedByMajor = false;

        $keyword = trim($request->get('keyword', ''));
        $category = $request->get('category', 'Tất cả danh mục');

        // Lấy danh sách 6 Từ khóa nổi bật được tìm kiếm nhiều nhất
        $topKeywords = LichSuTimKiem::select('TuKhoa', DB::raw('count(*) as total'))
            ->groupBy('TuKhoa')
            ->orderBy('total', 'desc')
            ->limit(6)
            ->pluck('TuKhoa');

        // Check trạng thái đăng nhập của sinh viên
        if (Session::has('user_id') && Session::get('role') === 'SinhVien') {
            $mssv = Session::get('MSSV');
            $sinhVien = SinhVien::find($mssv);

            // Lấy danh sách mã tài liệu mà sinh viên đã đánh dấu
            $markedBookIds = DanhDau::where('MSSV', $mssv)->pluck('MaTaiLieu')->toArray();

            // Ghi nhận lịch sử tìm kiếm vào CSDL
            if (!empty($keyword)) {
                LichSuTimKiem::create([
                    'MaLuotTimKiem'       => 'SK_' . time() . '_' . rand(100, 999),
                    'MSSV'              => $mssv,
                    'TuKhoa'           => $keyword,
                    'ThoiDiemTimKiem' => now(),
                ]);
            }

            // Gọi gợi ý tài liệu cá nhân hóa (Tự động lấy tham số Top-N & Ngưỡng từ CSDL)
            if ($sinhVien) {
                $hasActiveCourses = $sinhVien->hocPhans()->where('TrangThai', 'DangHoc')->exists();
                $isSuggestedByMajor = !$hasActiveCourses;

                $recommendedBooks = $this->recommendationService->getRecommendationsForStudent($mssv);
            }
        }

        if (!empty($keyword)) {
            $query = TaiLieu::query();
            $query->where(function ($q) use ($keyword) {
                $q->where('NhanDe', 'LIKE', "%{$keyword}%")
                  ->orWhere('TacGia', 'LIKE', "%{$keyword}%")
                  ->orWhere('ThuatNguChuDe', 'LIKE', "%{$keyword}%")
                  ->orWhere('MoTa', 'LIKE', "%{$keyword}%");
            });

            // Lọc thêm theo Danh mục
            if ($category === 'Sách in') {
                $query->where('MaTaiLieu', 'LIKE', 'HUB%');
            } elseif ($category === 'Sách điện tử') {
                $query->where('MaTaiLieu', 'LIKE', 'TLE%');
            } elseif ($category === 'Đề cương HP') {
                $query->where('MaTaiLieu', 'LIKE', 'DC%');
            }

            $searchResults = $query->paginate(12)->appends(request()->query());
        } else {
            $allBooks = TaiLieu::limit(12)->get();
        }

        return view('home', compact(
            'sinhVien', 
            'recommendedBooks', 
            'allBooks', 
            'searchResults', 
            'keyword', 
            'category',
            'markedBookIds',
            'topKeywords',
            'isSuggestedByMajor'
        ));
    }

    // API Gợi ý hoàn tất từ khóa Autocomplete AJAX
    public function autocomplete(Request $request)
    {
        $query = trim($request->get('query', ''));
        if (empty($query)) {
            return response()->json([]);
        }

        $suggestions = LichSuTimKiem::select('TuKhoa')
            ->where('TuKhoa', 'LIKE', "%{$query}%")
            ->distinct()
            ->limit(5)
            ->pluck('TuKhoa');

        return response()->json($suggestions);
    }

    // Xem thông tin chi tiết tài liệu & Danh sách học phần liên quan
    public function documentDetail($id)
    {
        $book = TaiLieu::findOrFail($id);

        $isMarked = false;
        $userFeedback = null;

        if (Session::has('user_id') && Session::get('role') === 'SinhVien') {
            $mssv = Session::get('MSSV');
            $isMarked = DanhDau::where('MSSV', $mssv)->where('MaTaiLieu', $id)->exists();
            $userFeedback = PhanHoi::where('MSSV', $mssv)->where('MaTaiLieu', $id)->first();
        }

        // Truy xuất danh sách các Học phần liên quan qua bảng DeCuongHocPhan
        $relatedCourses = DB::table('DeCuongHocPhan')
            ->join('HocPhan', 'DeCuongHocPhan.MaHocPhan', '=', 'HocPhan.MaHocPhan')
            ->where('DeCuongHocPhan.MaTaiLieu', $id)
            ->select('HocPhan.*', 'DeCuongHocPhan.NoiDungDeCuong', 'DeCuongHocPhan.LoaiTaiLieu')
            ->get();

        return view('document_detail', compact('book', 'isMarked', 'userFeedback', 'relatedCourses'));
    }

    // Đánh dấu / Bỏ đánh dấu tài liệu
    public function toggleBookmark(Request $request)
    {
        if (!Session::has('user_id') || Session::get('role') !== 'SinhVien') {
            return redirect()->back()->withErrors(['error' => 'Vui lòng đăng nhập tài khoản Sinh viên để sử dụng chức năng đánh dấu!']);
        }

        $mssv = Session::get('MSSV');
        $maTaiLieu = $request->input('MaTaiLieu');

        $danhDau = DanhDau::where('MSSV', $mssv)->where('MaTaiLieu', $maTaiLieu)->first();

        if ($danhDau) {
            $danhDau->delete();
            return redirect()->back()->with('success', 'Đã bỏ đánh dấu tài liệu khỏi danh sách yêu thích!');
        } else {
            DanhDau::create([
                'MaDanhDau'        => 'DD_' . time() . '_' . rand(10, 99),
                'MSSV'               => $mssv,
                'MaTaiLieu'        => $maTaiLieu,
                'ThoiDiemDanhDau' => now(),
            ]);
            return redirect()->back()->with('success', 'Đã đánh dấu tài liệu vào danh sách yêu thích!');
        }
    }

    // Màn hình xem danh sách tài liệu Sinh viên đã đánh dấu
    public function bookmarks()
    {
        if (!Session::has('user_id') || Session::get('role') !== 'SinhVien') {
            return redirect()->route('login');
        }

        $mssv = Session::get('MSSV');
        $sinhVien = SinhVien::find($mssv);

        $markedBooks = TaiLieu::join('DanhDau', 'TaiLieu.MaTaiLieu', '=', 'DanhDau.MaTaiLieu')
            ->where('DanhDau.MSSV', $mssv)
            ->orderBy('DanhDau.ThoiDiemDanhDau', 'desc')
            ->select('TaiLieu.*', 'DanhDau.ThoiDiemDanhDau')
            ->get();

        return view('bookmarks', compact('sinhVien', 'markedBooks'));
    }

    // Gửi phản hồi về mức độ phù hợp của gợi ý
    public function submitFeedback(Request $request)
    {
        if (!Session::has('user_id') || Session::get('role') !== 'SinhVien') {
            return redirect()->back()->withErrors(['error' => 'Vui lòng đăng nhập để gửi đánh giá!']);
        }

        $mssv = Session::get('MSSV');
        $maTaiLieu = $request->input('MaTaiLieu');
        $mucDanhGia = $request->input('MucDanhGia', 'PhuHop');

        PhanHoi::updateOrCreate(
            ['MSSV' => $mssv, 'MaTaiLieu' => $maTaiLieu],
            [
                'MaPhanHoi'  => 'PH_' . time() . '_' . rand(10, 99),
                'MucDanhGia' => $mucDanhGia,
                'ThoiDiem'    => now()
            ]
        );

        return redirect()->back()->with('success', 'Cảm ơn bạn đã phản hồi về mức độ phù hợp của gợi ý!');
    }

    // Xem thông tin cá nhân và danh sách học phần
    public function profile()
    {
        if (!Session::has('user_id')) {
            return redirect()->route('login');
        }

        $mssv = Session::get('MSSV');
        $sinhVien = SinhVien::with(['hocPhans', 'nganh'])->find($mssv);

        return view('profile', compact('sinhVien'));
    }

    // Xem lịch sử mượn trả
    public function history()
    {
        if (!Session::has('user_id')) {
            return redirect()->route('login');
        }

        $mssv = Session::get('MSSV');
        $sinhVien = SinhVien::find($mssv);

        $lichSuMuon = \App\Models\MuonTra::with('taiLieu')
            ->where('MSSV', $mssv)
            ->orderBy('NgayMuon', 'desc')
            ->get();

        return view('history', compact('sinhVien', 'lichSuMuon'));
    }
}