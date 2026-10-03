<?php
session_start();

$page_title = 'Add New Member';

// ==========================================
// 1. PUSH STYLES
// ==========================================
ob_start();
?>
<style>
    /* Top Toolbar */
    .page-toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
    .btn-back { display: inline-flex; align-items: center; gap: 8px; color: var(--text-muted); text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: color 0.2s; }
    .btn-back:hover { color: var(--primary); }

    /* Form Card Container */
    .form-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); overflow: hidden; }
    .form-header { padding: 1.5rem 2rem; border-bottom: 1px solid var(--border); background: #fafbfc; }
    .form-title { font-size: 1.25rem; font-weight: 700; color: var(--primary); }
    .form-subtitle { font-size: 0.85rem; color: var(--text-muted); margin-top: 4px; }
    .form-body { padding: 2rem; }

    /* Form Layout Grid */
    .form-section { margin-bottom: 2.5rem; }
    .form-section:last-child { margin-bottom: 0; }
    .section-title { font-size: 0.85rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 8px; }
    .section-title::after { content: ''; flex: 1; height: 1px; background: var(--border); }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }

    /* Form Controls */
    .input-group { display: flex; flex-direction: column; gap: 6px; }
    .input-label { font-size: 0.85rem; font-weight: 600; color: var(--text-main); }
    .input-label .required { color: var(--danger); margin-left: 2px; }

    .form-control { 
        width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 8px; 
        font-family: inherit; font-size: 0.95rem; color: var(--text-main); background: var(--surface);
        transition: border-color 0.2s, box-shadow 0.2s; outline: none;
    }
    .form-control:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(91, 125, 232, 0.15); }
    .form-control::placeholder { color: #cbd5e1; }

    /* --- Custom UI Dropdowns (Replaces native select) --- */
    .custom-select-wrapper { position: relative; width: 100%; user-select: none; }
    .custom-select-toggle {
        display: flex; justify-content: space-between; align-items: center;
        cursor: pointer; text-align: left;
    }
    .custom-select-toggle .select-placeholder { color: #cbd5e1; }
    .custom-select-toggle i { color: var(--text-muted); font-size: 0.8rem; transition: transform 0.2s; pointer-events: none; }
    
    .custom-select-wrapper.active .custom-select-toggle { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(91, 125, 232, 0.15); }
    .custom-select-wrapper.active .custom-select-toggle i { transform: rotate(180deg); color: var(--accent); }

    .custom-select-menu {
        position: absolute; top: calc(100% + 5px); left: 0; width: 100%;
        background: white; border: 1px solid var(--border); border-radius: 8px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        opacity: 0; visibility: hidden; transform: translateY(-10px);
        transition: all 0.2s ease; z-index: 50; padding: 0.5rem;
    }
    .custom-select-wrapper.active .custom-select-menu { opacity: 1; visibility: visible; transform: translateY(0); }
    
    .custom-select-item {
        padding: 0.6rem 0.75rem; border-radius: 6px; font-size: 0.9rem; font-weight: 500;
        color: var(--text-main); cursor: pointer; transition: background 0.2s, color 0.2s;
    }
    .custom-select-item:hover { background: var(--background); color: var(--primary); }
    .custom-select-item.selected { background: rgba(91, 125, 232, 0.1); color: var(--primary); font-weight: 600; }

    /* Form Footer */
    .form-footer { padding: 1.5rem 2rem; background: #fafbfc; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 1rem; }
    .btn { padding: 0.7rem 1.5rem; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; text-decoration: none; border: none; }
    .btn-outline { background: white; color: var(--text-main); border: 1px solid var(--border); }
    .btn-outline:hover { background: var(--background); border-color: var(--text-muted); }
    .btn-primary { background: var(--primary); color: white; box-shadow: 0 4px 6px -1px rgba(26, 54, 115, 0.2); }
    .btn-primary:hover { background: var(--primary-hover); transform: translateY(-1px); box-shadow: 0 6px 10px -1px rgba(26, 54, 115, 0.3); }

    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; gap: 1rem; }
        .form-footer { flex-direction: column-reverse; }
        .form-footer .btn { width: 100%; justify-content: center; }
    }
</style>
<?php
$page_styles = ob_get_clean();

// ==========================================
// 2. YIELD CONTENT
// ==========================================
ob_start();
?>
<div class="page-toolbar">
    <a href="admin_members" class="btn-back">
        <i class="fas fa-arrow-left"></i> Back to Members List
    </a>
</div>

<div class="form-card">
    <div class="form-header">
        <h2 class="form-title">Register New Member</h2>
        <p class="form-subtitle">Fill in the details below to add a new student, faculty, or staff member to the library system.</p>
    </div>

    <form action="" method="POST" id="addMemberForm">
        <div class="form-body">
            
            <!-- Section 1: Institutional Details -->
            <div class="form-section">
                <h3 class="section-title"><i class="fas fa-id-card"></i> Institutional Details</h3>
                <div class="form-grid">
                    <div class="input-group">
                        <label class="input-label">ID Number <span class="required">*</span></label>
                        <input type="text" name="member_id" class="form-control" placeholder="e.g., 2026-0001" required>
                    </div>
                    
                    <div class="input-group">
                        <label class="input-label">Member Type <span class="required">*</span></label>
                        <!-- Custom Select for Member Type -->
                        <div class="custom-select-wrapper" id="memberTypeWrapper">
                            <input type="hidden" name="member_type" id="memberTypeInput">
                            <button type="button" class="form-control custom-select-toggle" id="memberTypeToggle">
                                <span class="select-label select-placeholder">Select type...</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="custom-select-menu">
                                <div class="custom-select-item" data-value="Student">Student</div>
                                <div class="custom-select-item" data-value="Faculty">Faculty</div>
                                <div class="custom-select-item" data-value="Staff">Staff</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Personal Information -->
            <div class="form-section">
                <h3 class="section-title"><i class="fas fa-user"></i> Personal Information</h3>
                <div class="form-grid">
                    <div class="input-group">
                        <label class="input-label">First Name <span class="required">*</span></label>
                        <input type="text" name="first_name" class="form-control" placeholder="Enter first name" required>
                    </div>
                    <div class="input-group">
                        <label class="input-label">Last Name <span class="required">*</span></label>
                        <input type="text" name="last_name" class="form-control" placeholder="Enter last name" required>
                    </div>
                    <div class="input-group">
                        <label class="input-label">Email Address <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="email@nisu.edu.ph" required>
                    </div>
                    <div class="input-group">
                        <label class="input-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-control" placeholder="09XX-XXX-XXXX">
                    </div>
                </div>
            </div>

            <!-- Section 3: Academic Details -->
            <div class="form-section" id="academicSection">
                <h3 class="section-title"><i class="fas fa-graduation-cap"></i> Academic / Department Details</h3>
                <div class="form-grid">
                    <div class="input-group">
                        <label class="input-label" id="deptLabel">Course / Program <span class="required">*</span></label>
                        <input type="text" name="department" class="form-control" placeholder="e.g., BS Information Technology" required>
                    </div>
                    
                    <div class="input-group" id="yearLevelGroup">
                        <label class="input-label">Year Level</label>
                        <!-- Custom Select for Year Level -->
                        <div class="custom-select-wrapper" id="yearLevelWrapper">
                            <input type="hidden" name="year_level" id="yearLevelInput">
                            <button type="button" class="form-control custom-select-toggle" id="yearLevelToggle">
                                <span class="select-label select-placeholder">Select year...</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="custom-select-menu">
                                <div class="custom-select-item" data-value="1">1st Year</div>
                                <div class="custom-select-item" data-value="2">2nd Year</div>
                                <div class="custom-select-item" data-value="3">3rd Year</div>
                                <div class="custom-select-item" data-value="4">4th Year</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="form-footer">
            <a href="admin_members" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Member
            </button>
        </div>
    </form>
</div>

<?php
$page_content = ob_get_clean();

// ==========================================
// 3. PUSH SCRIPTS
// ==========================================
ob_start();
?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // --- Universal Custom Select Logic ---
        document.querySelectorAll('.custom-select-wrapper').forEach(wrapper => {
            const toggle = wrapper.querySelector('.custom-select-toggle');
            const label = toggle.querySelector('.select-label');
            const input = wrapper.querySelector('input[type="hidden"]');
            const items = wrapper.querySelectorAll('.custom-select-item');

            // Toggle Dropdown
            toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                // Close other open dropdowns
                document.querySelectorAll('.custom-select-wrapper.active').forEach(w => {
                    if (w !== wrapper) w.classList.remove('active');
                });
                wrapper.classList.toggle('active');
            });

            // Handle Item Selection
            items.forEach(item => {
                item.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const value = item.getAttribute('data-value');
                    
                    // Update hidden input
                    input.value = value;
                    
                    // Update UI Text & Remove Placeholder styling
                    label.innerText = item.innerText;
                    label.classList.remove('select-placeholder');

                    // Highlight selected item
                    items.forEach(i => i.classList.remove('selected'));
                    item.classList.add('selected');

                    // Close dropdown
                    wrapper.classList.remove('active');

                    // Dispatch a 'change' event so we can trigger the dynamic logic below
                    input.dispatchEvent(new Event('change'));
                });
            });
        });

        // Close dropdowns if clicked outside
        document.addEventListener('click', () => {
            document.querySelectorAll('.custom-select-wrapper').forEach(w => w.classList.remove('active'));
        });

        // --- Dynamic Academic Section Logic ---
        const memberTypeInput = document.getElementById('memberTypeInput');
        const deptLabel = document.getElementById('deptLabel');
        const deptInput = document.querySelector('input[name="department"]');
        const yearLevelGroup = document.getElementById('yearLevelGroup');
        const yearLevelInput = document.getElementById('yearLevelInput');
        const yearLevelToggleLabel = document.querySelector('#yearLevelToggle .select-label');
        const yearLevelItems = document.querySelectorAll('#yearLevelWrapper .custom-select-item');

        memberTypeInput.addEventListener('change', function() {
            const selectedType = this.value;

            if (selectedType === 'Student') {
                deptLabel.innerHTML = 'Course / Program <span class="required">*</span>';
                deptInput.placeholder = 'e.g., BS Information Technology';
                yearLevelGroup.style.display = 'flex'; 
            } else {
                deptLabel.innerHTML = 'Department / Office <span class="required">*</span>';
                deptInput.placeholder = 'e.g., College of Computer Studies';
                yearLevelGroup.style.display = 'none'; 
                
                // Clear Year Level UI completely if Faculty/Staff
                yearLevelInput.value = '';
                yearLevelToggleLabel.innerText = 'Select year...';
                yearLevelToggleLabel.classList.add('select-placeholder');
                yearLevelItems.forEach(i => i.classList.remove('selected'));
            }
        });
        
    });
</script>
<?php
$page_scripts = ob_get_clean();

// ==========================================
// 4. REQUIRE LAYOUT
// ==========================================
require 'admin_layout.php';
?>