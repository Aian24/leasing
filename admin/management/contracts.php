<?php require_once '../includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Contracts — LeasePro Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin-sections.css">
    <style>
        .status-Badge { padding: 4px 10px; border-radius: 8px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; }
        .status-Pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .status-Approved { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .status-Rejected { background: rgba(244, 63, 94, 0.1); color: #f43f5e; }
        .modal-Overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px); z-index: 1000; display: flex; items-center: center; justify-content: center; padding: 20px; }
        .contract-Card { background: #fff; width: 100%; max-width: 900px; border-radius: 2.5rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; display: flex; flex-direction: column; max-height: 90vh; border: 1px solid rgba(0,0,0,0.05); }
    </style>
</head>
<body class="bg-[#f8fafc]">
    <?php include '../includes/layout/sidebar.php'; ?>
    <div id="admin-main">
        <?php include '../includes/layout/header.php'; ?>
        <div id="admin-content" class="p-6">
            <div class="panel">
                <div class="panel-header">
                    <div>
                        <div class="panel-title">Contract Review Portal</div>
                        <div class="panel-subtitle">Manage and verify all submitted lease applications</div>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="table-toolbar">
                        <div class="search-box">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="contract-search" placeholder="Search by Ref#, Name, or Space...">
                        </div>
                    </div>
                    <div style="overflow-x:auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Submitted By</th>
                                    <th>Lessee/Company</th>
                                    <th>Stall Info</th>
                                    <th>Status</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="contracts-tbody">
                                <tr><td colspan="6" class="p-10 text-center"><i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading submissions...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <div id="review-modal" class="modal-Overlay hidden">
        <div class="contract-Card animate-fade-in-up">
            <div class="p-8 border-b flex justify-between items-center bg-slate-50/50">
                <div class="flex items-center gap-6">
                    <div>
                        <h2 class="text-2xl font-black text-slate-800" id="m-ref-title">Review Submission</h2>
                        <p class="text-[10px] text-slate-400 font-black uppercase tracking-[0.2em] mt-1">Full Application Dossier</p>
                    </div>
                    <div class="h-10 w-[1px] bg-slate-200"></div>
                    <div class="flex gap-2">
                        <button id="btn-approve" class="px-5 py-2.5 bg-emerald-600 text-white rounded-2xl font-bold hover:bg-emerald-700 transition shadow-lg shadow-emerald-600/20 text-xs flex items-center gap-2">
                            <i class="fa-solid fa-check-double"></i> Approve
                        </button>
                        <button id="btn-reject" class="px-5 py-2.5 bg-rose-600 text-white rounded-2xl font-bold hover:bg-rose-700 transition shadow-lg shadow-rose-600/20 text-xs flex items-center gap-2">
                            <i class="fa-solid fa-ban"></i> Reject
                        </button>
                        <button onclick="printCurrent()" class="px-5 py-2.5 bg-blue-600 text-white rounded-2xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-600/20 text-xs flex items-center gap-2">
                            <i class="fa-solid fa-print"></i> Print
                        </button>
                    </div>
                </div>
                <button onclick="closeReview()" class="w-12 h-12 rounded-full hover:bg-rose-50 hover:text-rose-500 text-slate-300 transition-all flex items-center justify-center"><i class="fa-solid fa-xmark text-2xl"></i></button>
            </div>
            <div class="p-10 overflow-y-auto bg-white" id="m-content">
                <!-- Content injected here -->
            </div>
        </div>
    </div>

    <!-- App System Modal -->
    <div id="app-modal" class="hidden fixed inset-0 z-[2000] flex items-center justify-center bg-slate-900/60 backdrop-blur-md p-4">
        <div class="bg-white dark:bg-slate-800 w-full max-w-sm rounded-[2.5rem] shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 animate-fade-in-up">
            <div class="p-10 text-center">
                <div id="app-modal-icon-bg" class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6 rotate-3">
                    <i id="app-modal-icon" class="text-2xl"></i>
                </div>
                <h3 id="app-modal-title" class="text-xl font-extrabold text-slate-800 dark:text-white mb-2">Confirmation</h3>
                <p id="app-modal-message" class="text-slate-500 dark:text-slate-400 text-sm font-medium leading-relaxed mb-8"></p>
                
                <div id="app-modal-buttons" class="flex flex-col gap-2">
                    <!-- Buttons dynamically added here -->
                </div>
            </div>
        </div>
    </div>

    <script src="../js/admin-core.js"></script>
    <script>
        let allContracts = [];
        let currentContractId = null;

        function showAppModal({ title, message, icon, iconBg, buttons }) {
            const modal = document.getElementById('app-modal');
            document.getElementById('app-modal-title').textContent = title;
            document.getElementById('app-modal-message').textContent = message;
            
            const iconEl = document.getElementById('app-modal-icon');
            iconEl.className = icon || 'fa-solid fa-circle-info';
            
            const iconBgEl = document.getElementById('app-modal-icon-bg');
            iconBgEl.className = `w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6 rotate-3 ${iconBg || 'bg-blue-100 text-blue-600'}`;

            const btnContainer = document.getElementById('app-modal-buttons');
            btnContainer.innerHTML = '';
            
            buttons.forEach(btn => {
                const b = document.createElement('button');
                b.className = btn.className || 'w-full py-3.5 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-2xl font-bold transition-all hover:bg-slate-200 dark:hover:bg-slate-600 text-sm';
                b.innerHTML = btn.text;
                b.onclick = () => {
                    modal.classList.add('hidden');
                    if (btn.onClick) btn.onClick();
                };
                btnContainer.appendChild(b);
            });
            
            modal.classList.remove('hidden');
        }

        async function loadContracts() {
            try {
                const res = await fetch('../../api/contract_api.php?action=list_all');
                const json = await res.json();
                if (json.success) {
                    allContracts = json.data;
                    renderTable(allContracts);
                }
            } catch (e) { console.error(e); }
        }

        function renderTable(list) {
            const tbody = document.getElementById('contracts-tbody');
            if (!list.length) {
                tbody.innerHTML = '<tr><td colspan="6" class="p-10 text-center text-slate-400">No submissions found.</td></tr>';
                return;
            }

            tbody.innerHTML = list.map(c => {
                let d = {}; try { d = JSON.parse(c.contract_data); } catch(e){}
                const name = d.lessee?.account_name || d['inp-account-name'] || 'N/A';
                const stall = d.stall?.location || d['inp-stall-code'] || 'N/A';
                
                return `
                    <tr>
                        <td class="font-mono text-[10px] font-black text-blue-600">${c.ref_no}</td>
                        <td class="font-bold text-slate-700">${c.user_name || 'System User'}</td>
                        <td class="text-slate-600 text-sm">${name}</td>
                        <td class="font-semibold text-slate-500 text-xs">${stall}</td>
                        <td><span class="status-Badge status-${c.status}">${c.status}</span></td>
                        <td class="text-right">
                            <button onclick="openReview(${c.id})" class="btn btn-primary btn-sm rounded-lg" style="padding:6px 12px; font-size:11px">
                                Review Application
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function openReview(id) {
            const c = allContracts.find(x => x.id === id);
            if (!c) return;
            currentContractId = id;
            
            let d = {}; try { d = JSON.parse(c.contract_data); } catch(e){}
            
            document.getElementById('m-ref-title').textContent = c.ref_no;
            
            const html = `
                <div class="grid grid-cols-3 gap-10">
                    <div class="col-span-1">
                        <div class="mb-8">
                            <h4 class="text-[10px] font-black text-blue-500 uppercase tracking-widest mb-6 border-b pb-2">Lessee Information</h4>
                            <div class="space-y-5">
                                <div><label class="text-[10px] text-slate-400 font-bold block uppercase mb-1">Company / Account</label><p class="text-base font-black text-slate-800">${d.lessee?.account_name || 'N/A'}</p></div>
                                <div><label class="text-[10px] text-slate-400 font-bold block uppercase mb-1">Trade Name</label><p class="text-sm font-bold text-slate-600">${d.lessee?.trade_name || 'N/A'}</p></div>
                                <div><label class="text-[10px] text-slate-400 font-bold block uppercase mb-1">Address</label><p class="text-xs text-slate-500 leading-relaxed">${d.lessee?.address || 'N/A'}</p></div>
                                <div><label class="text-[10px] text-slate-400 font-bold block uppercase mb-1">Nature of Business</label><span class="px-2 py-1 bg-slate-100 text-slate-600 text-[10px] font-black rounded-lg uppercase">${d.lessee?.nature_of_business || 'N/A'}</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-1">
                        <div class="mb-8">
                            <h4 class="text-[10px] font-black text-amber-500 uppercase tracking-widest mb-6 border-b pb-2">Stall & Spatial Details</h4>
                            <div class="space-y-5">
                                <div><label class="text-[10px] text-slate-400 font-bold block uppercase mb-1">Space Code / Stall No</label><p class="text-base font-black text-slate-800">${d.stall?.location || 'N/A'} (Stall #${d.stall?.stall_no || '?'})</p></div>
                                <div><label class="text-[10px] text-slate-400 font-bold block uppercase mb-1">Monthly Rent</label><p class="text-xl font-black text-emerald-600">₱${d.stall?.monthly_rent || '0.00'}</p></div>
                                <div><label class="text-[10px] text-slate-400 font-bold block uppercase mb-1">Area / Rate</label><p class="text-sm font-bold text-slate-600">${d.stall?.area || '0'} sqm <span class="text-slate-300 font-normal">@</span> ₱${d.stall?.rate || '0'}/sqm</p></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-1">
                        <div class="mb-8">
                            <h4 class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-6 border-b pb-2">Lease Timeline</h4>
                            <div class="space-y-5">
                                <div><label class="text-[10px] text-slate-400 font-bold block uppercase mb-1">Commencement</label><p class="text-sm font-bold text-slate-800">${d.terms?.start || 'N/A'}</p></div>
                                <div><label class="text-[10px] text-slate-400 font-bold block uppercase mb-1">Expiration</label><p class="text-sm font-bold text-slate-800">${d.terms?.end || 'N/A'}</p></div>
                                <div class="p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100/50">
                                    <label class="text-[9px] text-indigo-400 font-bold block uppercase mb-2">Duration Breakdown</label>
                                    <div class="flex gap-4">
                                        <div class="text-center"><span class="block text-lg font-black text-indigo-600">${d.terms?.years || '0'}</span><span class="text-[8px] uppercase font-bold text-indigo-300">Years</span></div>
                                        <div class="text-center"><span class="block text-lg font-black text-indigo-600">${d.terms?.months || '0'}</span><span class="text-[8px] uppercase font-bold text-indigo-300">Months</span></div>
                                        <div class="text-center"><span class="block text-lg font-black text-indigo-600">${d.terms?.days || '0'}</span><span class="text-[8px] uppercase font-bold text-indigo-300">Days</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-10 border-t border-slate-100">
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6">Secondary Contact & Tax Verification</h4>
                    <div class="grid grid-cols-3 gap-6">
                        <div class="p-5 bg-slate-50 rounded-3xl group hover:bg-white hover:shadow-xl hover:shadow-slate-200/50 transition-all border border-transparent hover:border-slate-100">
                            <i class="fa-solid fa-envelope text-blue-400 mb-3 block text-xl"></i>
                            <label class="text-[9px] text-slate-300 font-bold block uppercase mb-1">Email Address</label>
                            <p class="text-sm font-black text-slate-700 truncate">${d.lessee?.email || 'N/A'}</p>
                        </div>
                        <div class="p-5 bg-slate-50 rounded-3xl group hover:bg-white hover:shadow-xl hover:shadow-slate-200/50 transition-all border border-transparent hover:border-slate-100">
                            <i class="fa-solid fa-phone text-emerald-400 mb-3 block text-xl"></i>
                            <label class="text-[9px] text-slate-300 font-bold block uppercase mb-1">Mobile / Phone</label>
                            <p class="text-sm font-black text-slate-700">${d.lessee?.mobile || 'N/A'}</p>
                        </div>
                        <div class="p-5 bg-slate-50 rounded-3xl group hover:bg-white hover:shadow-xl hover:shadow-slate-200/50 transition-all border border-transparent hover:border-slate-100">
                            <i class="fa-solid fa-fingerprint text-amber-400 mb-3 block text-xl"></i>
                            <label class="text-[9px] text-slate-300 font-bold block uppercase mb-1">Tax (TIN)</label>
                            <p class="text-sm font-black text-slate-700">${d.lessee?.tin || 'N/A'}</p>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('m-content').innerHTML = html;
            document.getElementById('review-modal').classList.remove('hidden');
        }

        function closeReview() {
            document.getElementById('review-modal').classList.add('hidden');
        }

        async function updateStatus(newStatus) {
            if (!currentContractId) return;
            
            showAppModal({
                title: 'Confirm ' + newStatus,
                message: 'Are you sure you want to change this contract status to ' + newStatus + '? This will notify the user and update the records.',
                icon: newStatus === 'Approved' ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-minus',
                iconBg: newStatus === 'Approved' ? 'bg-green-100 text-green-600' : 'bg-rose-100 text-rose-600',
                buttons: [
                    {
                        text: 'Yes, Confirm ' + newStatus,
                        className: 'w-full py-3.5 bg-slate-900 text-white rounded-2xl font-bold shadow-lg shadow-slate-900/20 active:scale-95 transition-all text-sm',
                        onClick: async () => {
                            try {
                                const res = await fetch('../../api/contract_api.php?action=update_status', {
                                    method: 'POST',
                                    body: JSON.stringify({ id: currentContractId, status: newStatus })
                                });
                                const json = await res.json();
                                if (json.success) {
                                    showAppModal({
                                        title: 'Success!',
                                        message: 'The contract has been successfully ' + newStatus.toLowerCase() + '.',
                                        icon: 'fa-solid fa-sparkles',
                                        iconBg: 'bg-emerald-100 text-emerald-600',
                                        buttons: [{ text: 'Great', onClick: () => { closeReview(); loadContracts(); } }]
                                    });
                                } else {
                                    showAppModal({
                                        title: 'Failed',
                                        message: json.message || 'Update failed.',
                                        icon: 'fa-solid fa-circle-xmark',
                                        iconBg: 'bg-rose-100 text-rose-600',
                                        buttons: [{ text: 'Try Again' }]
                                    });
                                }
                            } catch (e) {
                                console.error(e);
                                showAppModal({
                                    title: 'System Error',
                                    message: 'A network error occurred while updating the status.',
                                    icon: 'fa-solid fa-wifi-slash',
                                    iconBg: 'bg-rose-100 text-rose-600',
                                    buttons: [{ text: 'Close' }]
                                });
                            }
                        }
                    },
                    { text: 'Cancel', onClick: () => {} }
                ]
            });
        }

        document.getElementById('btn-approve').onclick = () => updateStatus('Approved');
        document.getElementById('btn-reject').onclick = () => updateStatus('Rejected');
        
        function printCurrent() {
            if (!currentContractId) return;
            window.open('../../user/print_contract.php?id=' + currentContractId, '_blank');
        }

        document.getElementById('contract-search').oninput = (e) => {
            const q = e.target.value.toLowerCase();
            const filtered = allContracts.filter(c => 
                c.ref_no.toLowerCase().includes(q) || 
                (c.user_name || '').toLowerCase().includes(q)
            );
            renderTable(filtered);
        };

        // Initial Load
        loadContracts();
    </script>
</body>
</html>
