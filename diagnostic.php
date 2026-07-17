<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/diagnostics.php';

$publicId = required_string($_GET['session'] ?? null, 48);
if ($publicId === null) {
    $flow = diagnostic_flow($conn, required_string($_GET['flow'] ?? null, 120) ?? '');
    if ($flow === null) abort_request(404, 'diagnostic_not_found', 'This diagnostic is not available.');
    $session = diagnostic_start($conn, $flow);
    redirect('diagnostic.php?session=' . rawurlencode($session['public_id']));
}
$session = diagnostic_session($conn, $publicId);
if ($session === null) abort_request(404, 'diagnostic_session_not_found', 'This diagnostic session is unavailable or expired.');
$node = diagnostic_node($conn, (int) $session['version_id'], $session['current_node_key']);
if ($node === null) abort_request(500, 'diagnostic_invalid', 'This diagnostic needs editorial review.');
$options = [];
if ($node['node_type'] === 'question') { $s=$conn->prepare('SELECT option_key,label FROM diagnostic_options WHERE node_id=? ORDER BY sort_order'); $s->bind_param('i',$node['id']); $s->execute(); $r=$s->get_result(); while($o=$r->fetch_assoc())$options[]=$o; $s->close(); }
$resources=[]; if($node['node_type']==='outcome'){ $s=$conn->prepare('SELECT * FROM diagnostic_resources WHERE node_id=? ORDER BY sort_order');$s->bind_param('i',$node['id']);$s->execute();$r=$s->get_result();while($x=$r->fetch_assoc())$resources[]=$x;$s->close(); }
$pageTitle='Diagnostic: '.$node['title'].' | GuideMyPC'; include __DIR__.'/includes/header.php'; include __DIR__.'/includes/navbar.php'; ?>
<section class="section diagnostic" aria-labelledby="diagnostic-title"><p class="section-label">Guided diagnostic</p><h1 id="diagnostic-title"><?php echo e($node['title']); ?></h1><p><?php echo nl2br(e($node['prompt'])); ?></p>
<?php if($node['node_type']==='question'): ?><form method="POST" action="<?php echo e(application_url('diagnostic_action.php')); ?>"><fieldset><legend>Choose the answer that best matches what you observe.</legend><?php echo csrf_field(); ?><input type="hidden" name="session" value="<?php echo e($publicId); ?>"><?php foreach($options as $option): ?><label><input required type="radio" name="option" value="<?php echo e($option['option_key']); ?>"> <?php echo e($option['label']); ?></label><?php endforeach; ?></fieldset><button class="primary-btn" name="action" value="answer">Continue</button><button class="secondary-btn" name="action" value="back" formnovalidate>Back</button><button class="secondary-btn" name="action" value="restart" formnovalidate>Restart</button></form><?php else: ?><aside class="guide-warning"><p><strong>What this suggests:</strong> <?php echo e($node['evidence_text']); ?></p><p><strong>Risk:</strong> <?php echo e($node['risk_level'] ?: 'Review before acting'); ?> · <strong>Time:</strong> <?php echo e($node['estimated_time'] ?: 'Not specified'); ?></p><p><?php echo nl2br(e($node['backup_warning'] ?: 'Stop if a step could risk data or hardware.')); ?></p></aside><?php foreach($resources as $resource): ?><p><a class="primary-btn" href="<?php echo e(application_url($resource['resource_type']==='guide'?'guide.php?slug='.rawurlencode($resource['resource_slug']):'community.php')); ?>"><?php echo e($resource['label']); ?></a></p><?php endforeach; ?><form method="POST" action="<?php echo e(application_url('diagnostic_action.php')); ?>"><?php echo csrf_field(); ?><input type="hidden" name="session" value="<?php echo e($publicId); ?>"><button class="secondary-btn" name="action" value="back">Back</button><button class="secondary-btn" name="action" value="restart">Start again</button></form><?php endif; ?></section><?php include __DIR__.'/includes/footer.php';
