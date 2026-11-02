#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Đánh giá bám sát hệ thống Laravel:
- Không tái cài đặt TF-IDF, cosine hay tokenizer trong Python.
- Gọi trực tiếp App\\Services\\RecommendationService thông qua evaluation_bridge.php.
- Ground truth lấy từ tài liệu gắn với các đề cương của đúng nhóm học phần
  mà RecommendationService sử dụng cho từng sinh viên.
- Cấu hình hiệu lực được đọc từ cache mà RecommendationService vừa sử dụng.
"""

import argparse
import csv
import json
import logging
import subprocess
import sys
from datetime import datetime
from pathlib import Path

LOG = logging.getLogger("evaluation")


def resolve_project_dir(project_dir=None):
    candidates = []
    if project_dir:
        candidates.append(Path(project_dir).expanduser())
    for start in (Path(__file__).resolve().parent, Path.cwd()):
        candidates.extend([start, *start.parents])

    seen = set()
    for candidate in candidates:
        try:
            candidate = candidate.resolve()
        except OSError:
            continue
        if candidate in seen:
            continue
        seen.add(candidate)
        if (candidate / ".env").is_file() and (candidate / "artisan").is_file():
            return candidate

    raise FileNotFoundError(
        "Không tìm thấy thư mục Laravel (cần có cả .env và artisan). "
        "Hãy truyền --project-dir \"DUONG_DAN_LARAVEL\"."
    )


def run_laravel_bridge(project_dir: Path, php_executable="php", limit=0):
    bridge = project_dir / "evaluation_bridge.php"
    if not bridge.is_file():
        raise FileNotFoundError(
            f"Không tìm thấy {bridge}. Hãy chép evaluation_bridge.php vào thư mục gốc Laravel."
        )

    command = [php_executable, str(bridge), str(max(0, int(limit)))]
    completed = subprocess.run(
        command,
        cwd=str(project_dir),
        capture_output=True,
        text=True,
        encoding="utf-8",
        errors="replace",
        timeout=60 * 60,
    )

    stdout = completed.stdout.strip()
    if completed.returncode != 0:
        detail = stdout or completed.stderr.strip() or "PHP bridge kết thúc với lỗi không xác định."
        try:
            payload = json.loads(detail)
            detail = payload.get("error", detail)
        except json.JSONDecodeError:
            pass
        raise RuntimeError(f"Không gọi được RecommendationService:\n{detail}")

    try:
        payload = json.loads(stdout)
    except json.JSONDecodeError as exc:
        raise RuntimeError(
            "PHP bridge không trả về JSON hợp lệ. "
            f"STDOUT: {stdout[:1000]!r}; STDERR: {completed.stderr[:1000]!r}"
        ) from exc

    if not payload.get("success"):
        raise RuntimeError(payload.get("error", "PHP bridge báo lỗi không rõ nguyên nhân."))
    return payload


def calculate_metrics(recommended, relevant, k):
    recommended = [str(x["id"]) if isinstance(x, dict) else str(x)
                   for x in (recommended or [])][:k]
    relevant = set(map(str, relevant or []))
    hits = [doc_id for doc_id in recommended if doc_id in relevant]

    precision = len(hits) / k if k else 0.0
    recall = len(hits) / len(relevant) if relevant else 0.0

    mrr = 0.0
    for rank, doc_id in enumerate(recommended, 1):
        if doc_id in relevant:
            mrr = 1.0 / rank
            break

    dcg = sum(
        1.0 / __import__("math").log2(rank + 1)
        for rank, doc_id in enumerate(recommended, 1)
        if doc_id in relevant
    )
    ideal_count = min(len(relevant), k)
    idcg = sum(
        1.0 / __import__("math").log2(rank + 1)
        for rank in range(1, ideal_count + 1)
    )
    ndcg = dcg / idcg if idcg else 0.0
    hit_at_1 = 1.0 if recommended and recommended[0] in relevant else 0.0

    return {
        "precision_at_k": precision,
        "recall_at_k": recall,
        "mrr": mrr,
        "ndcg": ndcg,
        "hit_at_1": hit_at_1,
        "hits": len(hits),
        "total_rel": len(relevant),
    }


def evaluate(payload):
    config = payload.get("config") or {}
    top_n = int(config.get("TopN") or 0)
    threshold = float(config.get("NguongCosine") or 0.0)
    if top_n <= 0:
        raise RuntimeError(
            "Không xác định được TopN hiệu lực từ RecommendationService. "
            "Kiểm tra bảng CauHinhGoiY và cache app_latest_config."
        )

    details = []
    skipped_no_ground_truth = 0
    for student in payload.get("students", []):
        relevant = student.get("ground_truth") or []
        if not relevant:
            skipped_no_ground_truth += 1
            continue

        metrics = calculate_metrics(student.get("recommended", []), relevant, top_n)
        details.append({
            "MSSV": student.get("MSSV"),
            "MaNganh": student.get("MaNganh"),
            "profile_source": student.get("profile_source"),
            "profile_course_ids": student.get("profile_course_ids", []),
            "recommended": student.get("recommended", []),
            "ground_truth": relevant,
            **metrics,
        })

    if not details:
        raise RuntimeError(
            "Không có sinh viên nào có ground truth để đánh giá. "
            "Kiểm tra dữ liệu DeCuongHocPhan và quan hệ học phần của sinh viên."
        )

    metric_names = [
        "precision_at_k", "recall_at_k", "mrr", "ndcg", "hit_at_1"
    ]
    summary = {
        "mean_" + key: round(sum(row[key] for row in details) / len(details), 6)
        for key in metric_names
    }
    summary.update({
        "k_value": top_n,
        "total_students_from_bridge": payload.get("student_count", 0),
        "total_evaluated_students": len(details),
        "skipped_students_without_ground_truth": skipped_no_ground_truth,
    })

    return {
        "timestamp": payload.get("generated_at") or datetime.now().isoformat(),
        "evaluation_method": "Laravel RecommendationService trực tiếp",
        "config_info": config,
        "summary": summary,
        "details": details,
        "notes": [
            "Danh sách gợi ý được sinh trực tiếp bởi RecommendationService của Laravel; Python không tự tính lại TF-IDF/Cosine.",
            "Tokenizer, chuẩn hóa văn bản, stopwords, quy tắc chọn học phần, cold-start, loại trừ tài liệu đang mượn, ngưỡng, TopN và tie-break do chính code Laravel quyết định.",
            "Ground truth hiện là nhãn thay thế (proxy): tài liệu liên kết trong DeCuongHocPhan của các học phần dùng để tạo hồ sơ. Đây chưa phải đánh giá mức độ phù hợp do chuyên gia hoặc người học gán nhãn độc lập.",
            "Precision@K dùng mẫu số K (TopN), kể cả khi hệ thống trả về ít hơn K tài liệu."
        ],
    }


def export_results(result, output_dir: Path):
    output_dir.mkdir(parents=True, exist_ok=True)
    stamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    json_path = output_dir / f"eval_actual_service_{stamp}.json"
    csv_path = output_dir / f"eval_actual_service_{stamp}.csv"

    json_path.write_text(
        json.dumps(result, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )

    with csv_path.open("w", newline="", encoding="utf-8-sig") as f:
        writer = csv.writer(f)
        writer.writerow([
            "MSSV", "MaNganh", "NguonHoSo", "Precision@K", "Recall@K",
            "MRR", "NDCG@K", "Hit@1", "SoHit", "TongTaiLieuChuan",
            "DanhSachGoiY", "DanhSachTaiLieuChuan"
        ])
        for row in result["details"]:
            rec_ids = [
                f'{x.get("id")} ({x.get("score")})' for x in row["recommended"]
            ]
            writer.writerow([
                row["MSSV"], row["MaNganh"], row["profile_source"],
                row["precision_at_k"], row["recall_at_k"], row["mrr"],
                row["ndcg"], row["hit_at_1"], row["hits"], row["total_rel"],
                "; ".join(rec_ids), "; ".join(row["ground_truth"])
            ])
            summary_png = export_charts(result, output_dir, stamp)

            print(f"Ảnh biểu đồ: {summary_png}")

            print(f"\nĐã xuất báo cáo:\n JSON: {json_path}\n CSV : {csv_path}")

def export_charts(result, output_dir, stamp):
    import matplotlib
    matplotlib.use("Agg")
    import matplotlib.pyplot as plt

    summary = result["summary"]

    # Biểu đồ tổng hợp các chỉ số
    labels = [
        "Precision@K",
        "Recall@K",
        "MRR",
        "NDCG@K",
        "Hit Rate@1"
    ]

    values = [
        summary["mean_precision_at_k"],
        summary["mean_recall_at_k"],
        summary["mean_mrr"],
        summary["mean_ndcg"],
        summary["mean_hit_at_1"]
    ]

    fig, ax = plt.subplots(figsize=(10, 6))
    bars = ax.bar(labels, values)

    ax.set_title("Đánh giá chất lượng hệ thống gợi ý tài liệu")
    ax.set_ylabel("Giá trị")
    ax.set_ylim(0, 1.1)
    ax.grid(axis="y", linestyle="--", alpha=0.4)

    for bar, value in zip(bars, values):
        ax.text(
            bar.get_x() + bar.get_width() / 2,
            bar.get_height() + 0.02,
            f"{value:.3f}",
            ha="center"
        )

    fig.tight_layout()

    summary_path = output_dir / f"evaluation_summary_{stamp}.png"
    fig.savefig(summary_path, dpi=180)
    plt.close(fig)

    return summary_path
    
def main():
    parser = argparse.ArgumentParser(
        description="Đánh giá trực tiếp RecommendationService của Laravel."
    )
    parser.add_argument("--project-dir", help="Thư mục gốc Laravel, chứa .env và artisan.")
    parser.add_argument("--php", default="php", help="Đường dẫn PHP CLI nếu lệnh php không có trong PATH.")
    parser.add_argument("--limit", type=int, default=0,
                        help="Giới hạn số sinh viên để chạy thử; 0 = toàn bộ.")
    parser.add_argument("--output-dir", default="evaluation_logs",
                        help="Thư mục xuất báo cáo (mặc định: evaluation_logs trong Laravel).")
    args = parser.parse_args()

    project_dir = resolve_project_dir(args.project_dir)
    print(f"Laravel project: {project_dir}")
    print("Đang gọi RecommendationService trực tiếp; có thể mất thời gian tùy số sinh viên...")

    payload = run_laravel_bridge(project_dir, args.php, args.limit)
    result = evaluate(payload)

    config = result["config_info"]
    summary = result["summary"]
    print("\n" + "=" * 88)
    print("ĐÁNH GIÁ THEO KẾT QUẢ THỰC TẾ CỦA RECOMMENDATIONSERVICE")
    print(f"Cấu hình: {config.get('MaCauHinh')} | TopN={config.get('TopN')} | "
          f"Ngưỡng Cosine={config.get('NguongCosine')}")
    print(f"Nguồn cấu hình: {config.get('source')}")
    print("=" * 88)
    print(f"Số sinh viên được gọi service : {summary['total_students_from_bridge']}")
    print(f"Số sinh viên được đánh giá    : {summary['total_evaluated_students']}")
    print(f"Bỏ qua (không có nhãn chuẩn)  : {summary['skipped_students_without_ground_truth']}")
    print(f"Precision@{summary['k_value']}: {summary['mean_precision_at_k']:.4f}")
    print(f"Recall@{summary['k_value']}   : {summary['mean_recall_at_k']:.4f}")
    print(f"MRR                           : {summary['mean_mrr']:.4f}")
    print(f"NDCG@{summary['k_value']}     : {summary['mean_ndcg']:.4f}")
    print(f"Hit Rate@1                    : {summary['mean_hit_at_1'] * 100:.2f}%")
    print("=" * 88)

    output_dir = Path(args.output_dir)
    if not output_dir.is_absolute():
        output_dir = project_dir / output_dir
    export_results(result, output_dir)


if __name__ == "__main__":
    logging.basicConfig(level=logging.INFO)
    try:
        main()
    except Exception as exc:
        LOG.exception("Đánh giá thất bại")
        print(f"\nLỖI ĐÁNH GIÁ: {exc}", file=sys.stderr)
        sys.exit(1)
