<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminLibraryController;
use App\Http\Controllers\AdminSystemController;

// Trang chủ dành cho cả Khách và Sinh viên
Route::get('/', [HomeController::class, 'index']);
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/api/autocomplete', [HomeController::class, 'autocomplete'])->name('api.autocomplete');

// Điều hướng login mở Modal tại Trang chủ
Route::get('/login', function() {
    return redirect()->route('home')->with('show_login_modal', true);
})->name('login');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/profile', [HomeController::class, 'profile'])->name('profile');

Route::get('/document/{id}', [App\Http\Controllers\HomeController::class, 'documentDetail'])->name('document.detail');

Route::post('/bookmark/toggle', [HomeController::class, 'toggleBookmark'])->name('bookmark.toggle');
Route::get('/bookmarks', [HomeController::class, 'bookmarks'])->name('bookmarks');

Route::post('/feedback/submit', [HomeController::class, 'submitFeedback'])->name('feedback.submit');

Route::get('/history', [HomeController::class, 'history'])->name('history');

// Quản trị Thủ thư
Route::middleware(['web', \App\Http\Middleware\CheckAdminRole::class . ':QuanTriThuVien'])->prefix('admin/library')->name('admin.library.')->group(function () {
    Route::get('/dashboard', [AdminLibraryController::class, 'dashboard'])->name('dashboard');
    
    // Quản lý danh mục tài liệu
    Route::get('/documents', [AdminLibraryController::class, 'documentsIndex'])->name('documents.index');
    Route::post('/documents/store', [AdminLibraryController::class, 'documentsStore'])->name('documents.store');
    Route::post('/documents/update/{id}', [AdminLibraryController::class, 'documentsUpdate'])->name('documents.update');
    Route::delete('/documents/delete/{id}', [AdminLibraryController::class, 'documentsDelete'])->name('documents.delete');
    Route::post('/documents/import', [AdminLibraryController::class, 'documentsImport'])->name('documents.import');

    // Quản lý mượn trả
    Route::get('/borrow-return', [AdminLibraryController::class, 'borrowIndex'])->name('borrow.index');
    Route::post('/borrow-return/store', [AdminLibraryController::class, 'borrowStore'])->name('borrow.store');
    Route::post('/borrow-return/return/{id}', [AdminLibraryController::class, 'borrowReturnStatus'])->name('borrow.return');
    Route::post('/borrow-return/import', [AdminLibraryController::class, 'borrowImport'])->name('borrow.import');

});

// Quản trị Hệ thống
Route::middleware(['web', \App\Http\Middleware\CheckAdminRole::class . ':QuanTriHeThong'])->prefix('admin/system')->name('admin.system.')->group(function () {
    Route::get('/dashboard', [AdminSystemController::class, 'dashboard'])->name('dashboard');

    // Nhập dữ liệu đào tạo
    Route::get('/import-training-data', [AdminSystemController::class, 'importDataView'])->name('import.view');
    Route::post('/import-training-data/process', [AdminSystemController::class, 'processImportData'])->name('import.process');

    // Cập nhật mô hình 
    Route::post('/retrain-model', [AdminSystemController::class, 'retrainModel'])->name('model.retrain');

    // Cấu hình tham số gợi ý
    Route::get('/config', [AdminSystemController::class, 'configView'])->name('config.view');
    Route::post('/config/save', [AdminSystemController::class, 'saveConfig'])->name('config.save');

});