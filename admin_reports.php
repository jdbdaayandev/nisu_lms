<?php
session_start();
$page_title = 'System Reports';

// --- Mock Generated Reports ---
$reports = [
    ['id' => 'RPT-1001', 'type' => 'Circulation', 'date_range' => 'Sep 01 - Sep 30, 2026', 'generated_on' => '2026-10-01', 'status' => 'Ready'],
    ['id' => 'RPT-1002', 'type' => 'Overdue Items', 'date_range' => 'As of Oct 03, 2026', 'generated_on' => '2026-10-03', 'status' => 'Ready'],
    ['id' => 'RPT-1003', 'type' => 'Inventory', 'date_range' => 'Full Audit', 'generated_on' => '2026-08-15', 'status' => 'Archived'],
];

ob_start();
?>
<style>
    .page-toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
    .btn-primary { background: var(--primary); color: white; border: none; padding: 0.6rem 1.25rem; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s; }
    .btn-primary:hover { background: var(--primary-hover); }

    /* Summary Stats (Reused from Dashboard) */
    .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem; }
    .stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .stat-icon { width: 54px; height: 54px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; }
    .icon-blue { background: #e0e6ff; color: var(--primary); }
    .icon-green { background: var(--success-bg); color: var(--success); }
    .icon-orange { background: var(--warning-bg); color: var(--warning); }
    .stat-details { display: flex; flex-direction: column; }
    .stat-value { font-size: 1.75rem; font-weight: 800; color: var(--text-main); line-height: 1; margin-bottom: 4px; }
    .stat-label { font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }

    /* Chart Placeholder */
    .chart-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem; }
    .chart-title { font-size: 1.1rem; font-weight: 700; color: var(--text-main); }
    .chart-placeholder { height: 250px; background: repeating-linear-gradient(45deg, #f8fafc, #f8fafc 10px, #f1f5f9 10px, #f1f5f9 20px); border: 2px dashed var(--border); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-weight: 600; flex-direction: column; gap: 10px; }

    /* Table Styles */
    .table-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; }
    .admin-table { width: 100%; border-collapse: collapse; text-align: left; }
    .admin-table th { padding: 1rem 1.25rem; background: var(--background); font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; border-bottom: 1px solid var(--border); }
    .admin-table td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); font-size: 0.9rem; color: var(--text-main); }
    
    .badge { padding: 0.25rem 0.6rem; border-radius: 50px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
    .badge-ready { background: var(--success-bg); color: var(--success); }
    .badge-archived { background: var(--background); color: var(--text-muted); border: 1px solid var(--border); }
    
    .btn-icon { width: 32px; height: 32px; border-radius: 6px; border: 1px solid var(--border); background: white; color: var(--text-muted); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; text-decoration: none; }
    .btn-icon:hover { border-color: var(--primary); color: var(--primary); background: var(--background); }

    /* Simple Modal */
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 1000; justify-content: center; align-items: center; }
    .modal-overlay.active { display: flex; }
    .modal-content { background: var(--surface); border-radius: var(--radius); width: 400px; padding: 1.5rem; }
    .form-control { width: 100%; padding: 0.65rem 1rem; border: 1px solid var(--border); border-radius: 8px; font-family: inherit; font-size: 0.95rem; margin-top: 6px; margin-bottom: 1rem; outline: none; }
    .form-control:focus { border-color: var(--accent); }
</style>
<?php
$page_styles = ob_get_clean();
ob_start();
?>
<div class="page-toolbar">
    <h2 style="font-size: 1.25rem; color: var(--primary);">Analytics Overview</h2>
    <button class="btn-primary" onclick="document.getElementById('reportModal').classList.add('active')">
        <i class="fas fa-file-export"></i> Generate Report
    </button>
</div>

<!-- Stats -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon icon-blue"><i class="fas fa-exchange-alt"></i></div>
        <div class="stat-details"><span class="stat-value">1,245</span><span class="stat-label">Total Borrows This Month</span></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-orange"><i class="fas fa-clock"></i></div>
        <div class="stat-details"><span class="stat-value">42</span><span class="stat-label">Currently Overdue</span></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-green"><i class="fas fa-user-plus"></i></div>
        <div class="stat-details"><span class="stat-value">85</span><span class="stat-label">New Members (30 Days)</span></div>
    </div>
</div>

<!-- Chart Placeholder -->
<div class="chart-card">
    <div class="chart-header">
        <span class="chart-title">Borrowing Activity (Last 7 Days)</span>
    </div>
    <div class="chart-placeholder">
        <i class="fas fa-chart-bar" style="font-size: 2rem;"></i>
        <span>Chart.js or ApexCharts integration goes here</span>
    </div>
</div>

<!-- Reports Table -->
<div class="table-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Report ID</th>
                <th>Type</th>
                <th>Date Range</th>
                <th>Generated On</th>
                <th>Status</th>
                <th style="text-align: right;">Download</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reports as $rpt): ?>
            <tr>
                <td style="font-weight: 600; font-family: monospace;"><?= $rpt['id'] ?></td>
                <td style="font-weight: 600; color: var(--primary);"><?= $rpt['type'] ?></td>
                <td><?= $rpt['date_range'] ?></td>
                <td><?= $rpt['generated_on'] ?></td>
                <td><span class="badge <?= $rpt['status'] == 'Ready' ? 'badge-ready' : 'badge-archived' ?>"><?= $rpt['status'] ?></span></td>
                <td style="text-align: right;">
                    <a href="#" class="btn-icon" title="Download PDF"><i class="fas fa-file-pdf"></i></a>
                    <a href="#" class="btn-icon" title="Download CSV"><i class="fas fa-file-csv"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal-overlay" id="reportModal">
    <div class="modal-content">
        <h3 style="margin-bottom: 1rem; color: var(--primary);">Generate Report</h3>
        <label style="font-size: 0.85rem; font-weight: 600;">Report Type</label>
        <select class="form-control" style="appearance: auto;">
            <option>Circulation Summary</option>
            <option>Inventory Status</option>
            <option>Fines & Penalties</option>
            <option>Active Members List</option>
        </select>
        
        <label style="font-size: 0.85rem; font-weight: 600;">Time Period</label>
        <select class="form-control" style="appearance: auto;">
            <option>Last 7 Days</option>
            <option>This Month</option>
            <option>Last Month</option>
            <option>Custom Range</option>
        </select>
        
        <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 1rem;">
            <button class="btn-primary" style="background: white; color: var(--text-main); border: 1px solid var(--border);" onclick="document.getElementById('reportModal').classList.remove('active')">Cancel</button>
            <button class="btn-primary"><i class="fas fa-download"></i> Generate</button>
        </div>
    </div>
</div>
<?php
$page_content = ob_get_clean();
require 'admin_layout.php';
?>