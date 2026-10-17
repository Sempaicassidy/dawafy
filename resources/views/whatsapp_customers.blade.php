@extends('layouts.app')

@section('content')
<!-- Daawafy Executive WhatsApp Integration View -->
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-whatsapp me-2 text-info"></i> WhatsApp Customer Messaging & Receipts</h2>
        <p class="text-white-50 mb-0">Automated WhatsApp receipts, payment reminders, and instant message dispatch.</p>
    </div>
    <div>
        <button class="btn-laravel-green text-white shadow" data-bs-toggle="modal" data-bs-target="#sendWhatsappModal">
            <i class="bi bi-send me-1"></i> + Send WhatsApp Message
        </button>
    </div>
</div>

<?php if(isset($msg)): ?>
    <div class="alert alert-success rounded-pill px-4 mb-4 animate-fade-in d-flex justify-content-between align-items-center">
        <div><i class="bi bi-check-circle me-2"></i> <?= $msg ?></div>
        <?php if(!empty($wa_url)): ?>
            <a href="<?= $wa_url ?>" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 text-white fw-bold">
                <i class="bi bi-whatsapp me-1"></i> Open WhatsApp Now
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if(isset($error)): ?>
    <div class="alert alert-danger rounded-pill px-4 mb-4 animate-fade-in"><i class="bi bi-exclamation-triangle me-2"></i> <?= $error ?></div>
<?php endif; ?>

<!-- Stats Overview -->
<div class="row g-3 mb-4 animate-fade-in">
    <div class="col-md-4">
        <div class="metric-widget metric-green">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Sent WhatsApp Messages</div>
                    <div class="metric-value text-success"><?= count(array_filter($queue, fn($q) => $q['status'] === 'sent')) ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-whatsapp"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-widget metric-yellow">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Queued Messages</div>
                    <div class="metric-value text-warning"><?= count(array_filter($queue, fn($q) => $q['status'] === 'pending')) ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-hourglass-split"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-widget metric-blue">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Registered Contacts</div>
                    <div class="metric-value text-primary"><?= count($customers) ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-people-fill"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="card-laravel animate-fade-in">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary"><i class="bi bi-chat-dots me-2"></i> WhatsApp Dispatch History & Outbox Log</h5>
        <input type="text" class="form-control form-control-sm rounded-pill px-3 w-25" id="whatsappSearch" placeholder="Search log..." onkeyup="filterWhatsappTable()">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-laravel mb-0" id="whatsappTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Recipient Number</th>
                        <th>Message Type</th>
                        <th>Preview Content</th>
                        <th>Status</th>
                        <th>Date & Time</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($queue)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No WhatsApp message history logged yet.</td></tr>
                    <?php else: ?>
                        <?php foreach($queue as $idx => $q): ?>
                            <?php 
                                $digits = preg_replace('/[^0-9]/', '', $q['recipient_number']);
                                if(substr($digits, 0, 1) === '0') $digits = '255' . substr($digits, 1);
                                $direct_wa = "https://api.whatsapp.com/send?phone={$digits}&text=" . urlencode($q['message_content']);
                            ?>
                            <tr>
                                <td class="text-muted"><?= $idx + 1 ?></td>
                                <td><strong class="text-dark font-monospace">+<?= htmlspecialchars($digits) ?></strong></td>
                                <td><span class="badge-laravel-blue"><?= htmlspecialchars(ucfirst($q['message_type'])) ?></span></td>
                                <td class="small text-muted" style="max-width: 250px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                    <?= htmlspecialchars($q['message_content']) ?>
                                </td>
                                <td>
                                    <span class="badge-laravel-green"><i class="bi bi-check-all me-1"></i> Dispatched</span>
                                </td>
                                <td><small class="text-muted"><?= date('d M Y, H:i', strtotime($q['created_at'])) ?></small></td>
                                <td class="text-end">
                                    <a href="<?= $direct_wa ?>" target="_blank" class="btn btn-sm btn-laravel-green text-white rounded-pill px-3 shadow-sm">
                                        <i class="bi bi-whatsapp me-1"></i> Resend / Open
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Send WhatsApp Message -->
<div class="modal fade" id="sendWhatsappModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-whatsapp me-2"></i> Send Customer WhatsApp Notification</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('addons.whatsapp') }}">
        @csrf
        <input type="hidden" name="send_whatsapp" value="1">
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Select Customer <span class="text-danger">*</span></label>
                <select name="customer_id" class="form-select rounded-pill px-3" required>
                    <option value="">-- Choose Customer --</option>
                    <?php foreach($customers as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['phone']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Message Category <span class="text-danger">*</span></label>
                <select name="message_type" class="form-select rounded-pill px-3" required onchange="toggleCustomMessage(this.value)">
                    <option value="invoice">Latest Sales Invoice Receipt</option>
                    <option value="outstanding">Outstanding Balance Notice</option>
                    <option value="reminder">Refill Prescription Reminder</option>
                    <option value="custom">Custom Text Message</option>
                </select>
            </div>
            <div class="mb-3 d-none" id="customMsgGroup">
                <label class="fw-bold text-muted small mb-1">Custom Message Text</label>
                <textarea name="custom_message" class="form-control rounded-3" rows="3" placeholder="Type your custom notification message here..."></textarea>
            </div>
        </div>
        <div class="modal-footer border-0 bg-light">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold text-white"><i class="bi bi-send me-1"></i> Send via WhatsApp</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
<?php if(!empty($wa_url)): ?>
    // Auto launch WhatsApp Web / App on submit
    window.open('<?= $wa_url ?>', '_blank');
<?php endif; ?>

function toggleCustomMessage(type) {
    let grp = document.getElementById('customMsgGroup');
    if(type === 'custom') grp.classList.remove('d-none');
    else grp.classList.add('d-none');
}

function filterWhatsappTable() {
    let input = document.getElementById("whatsappSearch").value.toLowerCase();
    let rows = document.querySelectorAll("#whatsappTable tbody tr");
    rows.forEach(r => {
        let text = r.innerText.toLowerCase();
        r.style.display = text.includes(input) ? "" : "none";
    });
}
</script>
@endsection
