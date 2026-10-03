<?php
session_start();
$page_title = 'System Settings';

ob_start();
?>
<style>
    /* Settings Layout */
    .settings-layout { display: grid; grid-template-columns: 240px 1fr; gap: 2rem; }
    
    /* Vertical Nav */
    .settings-nav { display: flex; flex-direction: column; gap: 0.4rem; }
    .settings-tab { padding: 0.8rem 1rem; border-radius: 8px; color: var(--text-muted); font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 10px; border: none; background: transparent; text-align: left; }
    .settings-tab:hover { background: rgba(0,0,0,0.03); color: var(--text-main); }
    .settings-tab.active { background: var(--primary); color: white; }

    /* Settings Panel */
    .settings-panel { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); display: none; }
    .settings-panel.active { display: block; animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    
    .panel-header { padding: 1.5rem 2rem; border-bottom: 1px solid var(--border); }
    .panel-title { font-size: 1.1rem; font-weight: 700; color: var(--primary); }
    .panel-desc { font-size: 0.85rem; color: var(--text-muted); margin-top: 4px; }
    
    .panel-body { padding: 2rem; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
    
    /* Form Elements */
    .input-group { display: flex; flex-direction: column; gap: 6px; }
    .input-label { font-size: 0.85rem; font-weight: 600; color: var(--text-main); }
    .form-control { width: 100%; padding: 0.7rem 1rem; border: 1px solid var(--border); border-radius: 8px; font-family: inherit; font-size: 0.95rem; outline: none; transition: border-color 0.2s; }
    .form-control:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(91, 125, 232, 0.15); }
    
    .panel-footer { padding: 1.25rem 2rem; border-top: 1px solid var(--border); background: #fafbfc; display: flex; justify-content: flex-end; }
    .btn-primary { background: var(--primary); color: white; border: none; padding: 0.6rem 1.5rem; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background 0.2s; display: inline-flex; align-items: center; gap: 8px; }
    .btn-primary:hover { background: var(--primary-hover); }

    /* Custom Toggle Switch */
    .toggle-wrapper { display: flex; align-items: center; justify-content: space-between; padding: 1rem; border: 1px solid var(--border); border-radius: 8px; margin-bottom: 1rem; }
    .toggle-text { display: flex; flex-direction: column; }
    .toggle-title { font-size: 0.9rem; font-weight: 600; color: var(--text-main); }
    .toggle-desc { font-size: 0.75rem; color: var(--text-muted); }
    
    .switch { position: relative; display: inline-block; width: 44px; height: 24px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .3s; border-radius: 34px; }
    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
    input:checked + .slider { background-color: var(--success); }
    input:checked + .slider:before { transform: translateX(20px); }

    @media (max-width: 768px) {
        .settings-layout { grid-template-columns: 1fr; }
        .form-grid { grid-template-columns: 1fr; }
    }
</style>
<?php
$page_styles = ob_get_clean();
ob_start();
?>
<div class="settings-layout">
    
    <!-- Left Navigation -->
    <div class="settings-nav">
        <button class="settings-tab active" onclick="switchTab('general', this)"><i class="fas fa-sliders-h"></i> General Info</button>
        <button class="settings-tab" onclick="switchTab('circulation', this)"><i class="fas fa-exchange-alt"></i> Circulation Rules</button>
        <button class="settings-tab" onclick="switchTab('system', this)"><i class="fas fa-shield-alt"></i> System & Security</button>
    </div>

    <!-- Panels Container -->
    <div class="settings-content">
        
        <!-- General Info Panel -->
        <div id="panel-general" class="settings-panel active">
            <div class="panel-header">
                <h3 class="panel-title">General Library Information</h3>
                <p class="panel-desc">Update your institution's core details displayed on receipts and public pages.</p>
            </div>
            <div class="panel-body">
                <div class="form-grid">
                    <div class="input-group">
                        <label class="input-label">Institution / Library Name</label>
                        <input type="text" class="form-control" value="NISU Library System">
                    </div>
                    <div class="input-group">
                        <label class="input-label">Contact Email</label>
                        <input type="email" class="form-control" value="library@nisu.edu.ph">
                    </div>
                </div>
                <div class="input-group" style="margin-bottom: 1.5rem;">
                    <label class="input-label">Physical Address</label>
                    <input type="text" class="form-control" value="San Jose del Monte, Central Luzon, Philippines">
                </div>
            </div>
            <div class="panel-footer">
                <button class="btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            </div>
        </div>

        <!-- Circulation Rules Panel -->
        <div id="panel-circulation" class="settings-panel">
            <div class="panel-header">
                <h3 class="panel-title">Circulation & Fine Rules</h3>
                <p class="panel-desc">Configure borrowing limits, durations, and penalty computations.</p>
            </div>
            <div class="panel-body">
                <div class="form-grid">
                    <div class="input-group">
                        <label class="input-label">Max Books per Student</label>
                        <input type="number" class="form-control" value="3">
                    </div>
                    <div class="input-group">
                        <label class="input-label">Default Loan Period (Days)</label>
                        <input type="number" class="form-control" value="7">
                    </div>
                </div>
                <div class="form-grid">
                    <div class="input-group">
                        <label class="input-label">Overdue Fine per Day (₱)</label>
                        <input type="number" class="form-control" value="5.00" step="0.50">
                    </div>
                    <div class="input-group">
                        <label class="input-label">Max Fine Limit (₱)</label>
                        <input type="number" class="form-control" value="500.00">
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <button class="btn-primary"><i class="fas fa-save"></i> Save Rules</button>
            </div>
        </div>

        <!-- System & Security Panel -->
        <div id="panel-system" class="settings-panel">
            <div class="panel-header">
                <h3 class="panel-title">System & Security</h3>
                <p class="panel-desc">Manage technical features, backups, and notifications.</p>
            </div>
            <div class="panel-body">
                
                <div class="toggle-wrapper">
                    <div class="toggle-text">
                        <span class="toggle-title">Automated Email Reminders</span>
                        <span class="toggle-desc">Send emails to students 1 day before due date</span>
                    </div>
                    <label class="switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="toggle-wrapper">
                    <div class="toggle-text">
                        <span class="toggle-title">Require Student ID Scan</span>
                        <span class="toggle-desc">Force barcode scanning during checkout process</span>
                    </div>
                    <label class="switch">
                        <input type="checkbox">
                        <span class="slider"></span>
                    </label>
                </div>

            </div>
            <div class="panel-footer">
                <button class="btn-primary"><i class="fas fa-save"></i> Save Settings</button>
            </div>
        </div>

    </div>
</div>

<?php
$page_content = ob_get_clean();
ob_start();
?>
<script>
    function switchTab(tabId, btnElement) {
        // Remove active class from all tabs
        document.querySelectorAll('.settings-tab').forEach(btn => btn.classList.remove('active'));
        // Hide all panels
        document.querySelectorAll('.settings-panel').forEach(panel => panel.classList.remove('active'));
        
        // Add active class to clicked tab
        btnElement.classList.add('active');
        // Show target panel
        document.getElementById('panel-' + tabId).classList.add('active');
    }
</script>
<?php
$page_scripts = ob_get_clean();
require 'admin_layout.php';
?>