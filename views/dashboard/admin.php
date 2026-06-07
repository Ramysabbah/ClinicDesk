<?php
/**
 * @var array $roleCounts - Count of users by role
 * @var int $todayCount - Count of appointments today
 * @var array $weekByStatus - Weekly appointments by status
 * @var array[] $recentAppts - Recent appointments
 * @var array[] $chartData - Chart data for appointments
 */
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
require_once __DIR__ . '/../../views/partials/sidebar.php';
require_once __DIR__ . '/../../views/partials/topbar.php';
require_once __DIR__ . '/../../views/partials/alerts.php';
?>

<!-- Stat Cards -->
<div class="row">
  <div class="col-lg-3 col-6">
    <div class="small-box bg-info">
      <div class="inner">
        <h3><?= array_sum($roleCounts) ?></h3>
        <p>Total Users</p>
      </div>
      <div class="icon"><i class="fas fa-users"></i></div>
      <a href="<?= BASE_URL ?>/index.php?page=users" class="small-box-footer">
        More info <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-success">
      <div class="inner">
        <h3><?= $roleCounts['doctor'] ?? 0 ?></h3>
        <p>Doctors</p>
      </div>
      <div class="icon"><i class="fas fa-user-md"></i></div>
      <a href="<?= BASE_URL ?>/index.php?page=doctors" class="small-box-footer">
        More info <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning">
      <div class="inner">
        <h3><?= $todayCount ?></h3>
        <p>Today's Appointments</p>
      </div>
      <div class="icon"><i class="fas fa-calendar-check"></i></div>
      <a href="<?= BASE_URL ?>/index.php?page=appointments&action=list" class="small-box-footer">
        More info <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-danger">
      <div class="inner">
        <h3><?= $weekByStatus['pending'] ?? 0 ?></h3>
        <p>Pending This Week</p>
      </div>
      <div class="icon"><i class="fas fa-clock"></i></div>
      <a href="<?= BASE_URL ?>/index.php?page=appointments&action=list&status=pending" class="small-box-footer">
        More info <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>
</div>

<div class="row">
  <!-- Chart -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header border-0">
        <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Appointments — Last 14 Days</h3>
      </div>
      <div class="card-body">
        <canvas id="apptChart" style="height:250px;"></canvas>
      </div>
    </div>
  </div>

  <!-- Week Summary -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header border-0">
        <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>This Week</h3>
      </div>
      <div class="card-body">
        <?php
        $total = array_sum($weekByStatus);
        $items = [
            ['Pending',   'warning', 'pending'],
            ['Confirmed', 'info',    'confirmed'],
            ['Completed', 'success', 'completed'],
            ['Cancelled', 'danger',  'cancelled'],
        ];
        foreach ($items as [$label, $color, $key]):
            $count = $weekByStatus[$key] ?? 0;
            $pct   = $total > 0 ? round($count / $total * 100) : 0;
        ?>
        <div class="mb-3">
          <div class="d-flex justify-content-between mb-1">
            <span><?= $label ?></span>
            <span class="badge badge-<?= $color ?>"><?= $count ?></span>
          </div>
          <div class="progress progress-sm">
            <div class="progress-bar bg-<?= $color ?>" style="width:<?= $pct ?>%"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- Recent Appointments -->
<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-list mr-2"></i>Recent Appointments</h3>
    <div class="card-tools">
      <a href="<?= BASE_URL ?>/index.php?page=appointments&action=list" class="btn btn-sm btn-primary">
        View All
      </a>
    </div>
  </div>
  <div class="card-body p-0">
    <table class="table table-striped table-hover">
      <thead>
        <tr><th>Patient</th><th>Doctor</th><th>Specialization</th><th>Date</th><th>Time</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php foreach ($recentAppts as $a): ?>
        <tr>
          <td><?= e($a['patient_name']) ?></td>
          <td>Dr. <?= e($a['doctor_name']) ?></td>
          <td><?= e($a['specialization']) ?></td>
          <td><?= formatDate($a['appt_date']) ?></td>
          <td><?= formatTime($a['appt_time']) ?></td>
          <td><span class="badge badge-<?= statusBadge($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($recentAppts)): ?>
        <tr><td colspan="6" class="text-center text-muted py-3">No appointments yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php
$labels = []; $counts = [];
$dateMap = [];
foreach ($chartData as $row) $dateMap[$row['appt_date']] = (int)$row['total'];
for ($i = 13; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('d M', strtotime($d));
    $counts[]  = $dateMap[$d] ?? 0;
}
$extraJs = '<script>
var ctx = document.getElementById("apptChart").getContext("2d");
new Chart(ctx, {
  type: "line",
  data: {
    labels: ' . json_encode($labels) . ',
    datasets: [{
      label: "Appointments",
      data: ' . json_encode($counts) . ',
      backgroundColor: "rgba(60,141,188,0.2)",
      borderColor: "#3c8dbc",
      borderWidth: 2,
      tension: 0.4,
      fill: true,
      pointRadius: 4,
      pointBackgroundColor: "#3c8dbc"
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, ticks: { stepSize: 1 } }
    }
  }
});
</script>';
require_once __DIR__ . '/../../views/partials/footer.php';
?>
