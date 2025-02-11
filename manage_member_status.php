<?php
session_start();
include 'dbconnect.php';

// 设置每页显示的记录数
$records_per_page = 10;

// 获取当前页码
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// 获取总记录数
$total_records_sql = "SELECT COUNT(*) as count FROM tb_member";
$total_records_result = mysqli_query($conn, $total_records_sql);
$total_records = mysqli_fetch_assoc($total_records_result)['count'];
$total_pages = ceil($total_records / $records_per_page);

// 使用LIMIT和OFFSET获取当前页的记录
$sql = "SELECT m.*, ms.status 
        FROM tb_member m 
        LEFT JOIN tb_member_status ms 
        ON m.employeeID = ms.employeeID 
        ORDER BY m.memberName ASC 
        LIMIT $records_per_page OFFSET $offset";
$result = mysqli_query($conn, $sql);

// 定义所有可能的状态
$statusOptions = ['Aktif', 'Berhenti', 'Pencen', 'Aktif/Pencen'];

// 定义状态颜色映射
function getStatusColor($status) {
    switch($status) {
        case 'Aktif':
            return 'success';
        case 'Berhenti':
            return 'danger';
        case 'Pencen':
            return 'info';
        case 'Aktif/Pencen':
            return 'primary';
        default:
            return 'secondary';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengurusan Status Anggota</title>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <?php include 'headeradmin.php'; ?>
    
    <div class="container">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h3 class="card-title mb-0">Pengurusan Status Anggota</h3>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <label class="me-2">Papar</label>
                        <select class="form-select form-select-sm me-2" style="width: 70px;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <label>rekod</label>
                    </div>
                    <div class="d-flex align-items-center">
                        <label class="me-2">Carian:</label>
                        <input type="search" class="form-control form-control-sm" style="width: 200px;">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="memberTable">
                        <thead class="table-light">
                            <tr>
                                <th>ID Anggota</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) { 
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['employeeID']); ?></td>
                                    <td><?php echo htmlspecialchars($row['memberName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td>
                                        <?php 
                                        $statusClass = match($row['status'] ?? 'Aktif') {
                                            'Aktif' => 'success',
                                            'Berhenti' => 'danger',
                                            'Pencen' => 'info',
                                            'Aktif/Pencen' => 'primary',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?php echo $statusClass; ?> rounded-pill">
                                            <?php echo htmlspecialchars($row['status'] ?? 'Aktif'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-primary btn-sm rounded-pill px-3"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#updateModal"
                                                data-id="<?php echo $row['employeeID']; ?>"
                                                data-status="<?php echo $row['status'] ?? 'Aktif'; ?>">
                                            Kemaskini
                                        </button>
                                    </td>
                                </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center'>Tiada data dijumpai</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- 在表格下方添加分页 -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Paparan dari <?php echo $offset + 1; ?> hingga 
                        <?php echo min($offset + $records_per_page, $total_records); ?> 
                        dari <?php echo $total_records; ?> rekod
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            <!-- 首页 -->
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=1" aria-label="First">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            
                            <!-- 上一页 -->
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                    <span aria-hidden="true">‹</span>
                                </a>
                            </li>

                            <?php
                            // 显示页码
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);

                            for ($i = $start_page; $i <= $end_page; $i++) {
                                echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '">';
                                echo '<a class="page-link" href="?page=' . $i . '">' . $i . '</a>';
                                echo '</li>';
                            }
                            ?>

                            <!-- 下一页 -->
                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                    <span aria-hidden="true">›</span>
                                </a>
                            </li>

                            <!-- 末页 -->
                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $total_pages; ?>" aria-label="Last">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="updateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Kemaskini Status Anggota</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="updateForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="employeeID" id="employeeID">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Berhenti">Berhenti</option>
                                <option value="Pencen">Pencen</option>
                                <option value="Aktif/Pencen">Aktif/Pencen</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // 处理模态框数据
        $('#updateModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var status = button.data('status');
            
            var modal = $(this);
            modal.find('#employeeID').val(id);
            modal.find('select[name="status"]').val(status);
        });

        // 处理表单提交
        $('#updateForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                type: 'POST',
                url: 'update_member_status.php',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        // 更新状态显示
                        var employeeID = $('#employeeID').val();
                        var newStatus = $('select[name="status"]').val();
                        
                        // 关闭模态框
                        $('#updateModal').modal('hide');
                        
                        // 显示成功消息
                        alert('Status berjaya dikemaskini');
                        
                        // 刷新页面
                        location.reload();
                    } else {
                        alert('Ralat: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Ralat sistem');
                }
            });
        });

        // 监听侧边栏状态变化
        if($('body').hasClass('sidebar-open')) {
            $('body').addClass('sidebar-open');
        }

        // 监听侧边栏切换按钮点击
        $('.sidebar-toggle').on('click', function() {
            $('body').toggleClass('sidebar-open');
        });
    });
    </script>

    <style>
    /* 基础容器样式 - 默认状态（sidebar关闭） */
    .container {
        margin-top: 80px;
        margin-left: 100px;
        width: calc(100% - 30px);
        transition: all 0.3s ease;
        padding: 0 20px;
    }

    /* 当侧边栏打开时的样式 */
    body.sidebar-open .container {
        margin-left: 250px;
        width: calc(100% - 280px);
    }

    /* 移除任何可能导致额外空白的样式 */
    body {
        padding: 0;
        margin: 0;
    }

    /* 确保内容紧跟在 header 后面 */
    .card {
        margin-top: 0;
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    /* 响应式调整 */
    @media (max-width: 768px) {
        .container {
            margin-left: 0 !important;
            width: 100% !important;
            padding: 0 10px;
        }
    }

    .table th {
        font-weight: 600;
        color: #495057;
    }

    .table td {
        vertical-align: middle;
    }

    .badge {
        font-weight: 500;
        padding: 0.5em 1em;
    }

    .btn-sm {
        font-size: 0.875rem;
    }

    /* 改进搜索框样式 */
    .form-control-sm {
        border-radius: 20px;
        padding-left: 15px;
        padding-right: 15px;
    }

    /* 改进选择框样式 */
    .form-select-sm {
        border-radius: 20px;
    }

    /* DataTables 自定义样式 */
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #0d6efd !important;
        color: white !important;
        border: none;
        border-radius: 20px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #0b5ed7 !important;
        color: white !important;
        border: none;
        border-radius: 20px;
    }

    /* 表格hover效果 */
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }

    /* 分页样式 */
    .pagination {
        margin: 0;
    }

    .page-link {
        padding: 0.375rem 0.75rem;
        color: #20c997;
        background-color: #fff;
        border: 1px solid #dee2e6;
    }

    .page-item.active .page-link {
        background-color: #20c997;
        border-color: #20c997;
        color: white;
    }

    .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        background-color: #fff;
        border-color: #dee2e6;
    }

    .page-link:hover {
        color: #198754;
        background-color: #e9ecef;
        border-color: #dee2e6;
    }

    .page-link:focus {
        box-shadow: 0 0 0 0.25rem rgba(32, 201, 151, 0.25);
    }
    </style>
</body>
</html>