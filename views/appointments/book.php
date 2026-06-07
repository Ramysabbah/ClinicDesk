<?php
/**
 * @var array[] $allDoctors - List of all doctors
 */
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
require_once __DIR__ . '/../../views/partials/sidebar.php';
require_once __DIR__ . '/../../views/partials/topbar.php';
require_once __DIR__ . '/../../views/partials/alerts.php';
?>
<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card card-primary">
  <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-plus mr-2"></i>Book New Appointment</h3></div>
  <div class="card-body">
    <form method="POST" action="<?= BASE_URL ?>/index.php?page=appointments&action=store">
      <?= CSRF::field() ?>

      <div class="form-group">
        <label>Select Doctor <span class="text-danger">*</span></label>
        <select name="doctor_id" id="doctorSelect" class="form-control" required onchange="updateDoctorInfo()">
          <option value="">— Choose a doctor —</option>
          <?php foreach($allDoctors as $d): ?>
          <option value="<?= $d['id'] ?>"
                  data-days="<?= e($d['available_days']) ?>"
                  data-fee="<?= e($d['consultation_fee']) ?>"
                  data-spec="<?= e($d['specialization_name']) ?>"
                  <?= ($_POST['doctor_id']??'')==$d['id']?'selected':'' ?>>
            Dr. <?= e($d['name']) ?> — <?= e($d['specialization_name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div id="doctorInfo" class="callout callout-info d-none">
        <b>Specialization:</b> <span id="infoSpec"></span> |
        <b>Fee:</b> ₪<span id="infoFee"></span> |
        <b>Available:</b> <span id="infoDays"></span>
      </div>

      <div class="form-group">
        <label>Appointment Date <span class="text-danger">*</span></label>
        <input type="date" name="appt_date" id="apptDate" class="form-control" required
               min="<?= date('Y-m-d') ?>" value="<?= e($_POST['appt_date']??'') ?>">
        <small id="dayWarning" class="text-danger d-none"></small>
      </div>

      <div class="form-group">
        <label>Time Slot <span class="text-danger">*</span></label>
        <div class="row">
          <?php foreach(APPOINTMENT_SLOTS as $slot): ?>
          <div class="col-4 col-sm-3 mb-2">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="appt_time"
                     id="slot_<?= str_replace(':','_',$slot) ?>" value="<?= $slot ?>"
                     <?= ($_POST['appt_time']??'')===$slot?'checked':'' ?>>
              <label class="form-check-label btn btn-sm btn-outline-primary w-100 text-center"
                     for="slot_<?= str_replace(':','_',$slot) ?>">
                <?= formatTime($slot) ?>
              </label>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="form-group">
        <label>Reason for Visit</label>
        <textarea name="reason" class="form-control" rows="3"
                  placeholder="Briefly describe your symptoms…"><?= e($_POST['reason']??'') ?></textarea>
      </div>

      <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-check mr-1"></i>Confirm Appointment
      </button>
    </form>
  </div>
</div>
</div>
</div>
<?php
$extraJs = <<<'JS'
<script>
var doctorData = {};
document.querySelectorAll('#doctorSelect option[data-days]').forEach(function(opt) {
  doctorData[opt.value] = { days: opt.dataset.days ? opt.dataset.days.split(',') : [], fee: opt.dataset.fee, spec: opt.dataset.spec };
});
function updateDoctorInfo() {
  var id = document.getElementById('doctorSelect').value;
  var info = document.getElementById('doctorInfo');
  if (!id || !doctorData[id]) { info.classList.add('d-none'); return; }
  document.getElementById('infoSpec').textContent = doctorData[id].spec;
  document.getElementById('infoFee').textContent  = parseFloat(doctorData[id].fee).toFixed(2);
  document.getElementById('infoDays').textContent  = doctorData[id].days.join(', ');
  info.classList.remove('d-none');
  checkDay();
}
function checkDay() {
  var id = document.getElementById('doctorSelect').value;
  var date = document.getElementById('apptDate').value;
  var warn = document.getElementById('dayWarning');
  warn.classList.add('d-none');
  if (!id || !date || !doctorData[id]) return;
  var dayMap = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
  var d = new Date(date + 'T12:00:00');
  var day = dayMap[d.getDay()];
  if (!doctorData[id].days.includes(day)) {
    warn.textContent = 'This doctor is not available on ' + d.toLocaleDateString('en', {weekday:'long'}) + '.';
    warn.classList.remove('d-none');
  }
}
document.getElementById('apptDate').addEventListener('change', checkDay);
updateDoctorInfo();
// Style selected radio as active button
document.querySelectorAll('input[name="appt_time"]').forEach(function(radio) {
  radio.addEventListener('change', function() {
    document.querySelectorAll('.btn-outline-primary').forEach(function(btn){ btn.classList.remove('active'); });
    if (this.checked) this.nextElementSibling.classList.add('active');
  });
  if (radio.checked) radio.nextElementSibling.classList.add('active');
});
</script>
JS;
require_once __DIR__ . '/../../views/partials/footer.php';
?>
