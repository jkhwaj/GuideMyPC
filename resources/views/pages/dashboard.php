<?php

declare(strict_types=1);

/** @var array<string, mixed> $dashboard */
/** @var string $role */
$isOperational = $dashboard['kind'] === 'operational';
?>
<section class="dashboard-page" aria-labelledby="dashboard-title">
    <aside class="dashboard-sidebar" aria-label="Dashboard navigation">
        <div>
            <p class="dashboard-kicker">GuideMyPC workspace</p>
            <p class="dashboard-role"><?php echo e(ucfirst($role)); ?></p>
        </div>
        <nav>
            <a aria-current="page" href="<?php echo e(application_url('dashboard.php')); ?>">Overview</a>
            <a href="<?php echo e(application_url('profile.php')); ?>">Profile</a>
            <a href="<?php echo e(application_url('guides.php')); ?>">Guides</a>
            <a href="<?php echo e(application_url('knowledge.php')); ?>">Knowledge</a>
            <?php if ($role === 'admin'): ?>
                <a href="<?php echo e(application_url('admin.php')); ?>">Admin tools</a>
            <?php endif; ?>
        </nav>
    </aside>

    <div class="dashboard-main">
        <header class="dashboard-heading">
            <div>
                <p class="dashboard-kicker"><?php echo $isOperational ? 'Content operations' : 'Your support activity'; ?></p>
                <h1 id="dashboard-title"><?php echo $isOperational ? 'System overview' : 'My dashboard'; ?></h1>
            </div>
            <p><?php echo $isOperational ? 'Published content and current activity across GuideMyPC.' : 'Continue troubleshooting where you left off.'; ?></p>
        </header>

        <div class="dashboard-kpis" aria-label="Dashboard summaries">
            <?php foreach ($dashboard['metrics'] as $metric): ?>
                <article class="dashboard-kpi">
                    <p><?php echo e($metric['label']); ?></p>
                    <strong><?php echo number_format((int) $metric['value']); ?></strong>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($isOperational): ?>
            <div class="dashboard-chart-grid">
                <section class="dashboard-panel" aria-labelledby="category-chart-title">
                    <div class="dashboard-panel-header">
                        <div>
                            <p class="dashboard-kicker">Publication mix</p>
                            <h2 id="category-chart-title">Content by category</h2>
                        </div>
                    </div>
                    <div class="dashboard-chart-frame">
                        <canvas data-dashboard-chart="categories" role="img" aria-label="Published guides and knowledge articles by category"></canvas>
                    </div>
                    <script type="application/json" data-dashboard-chart-data="categories"><?php echo json_encode($dashboard['categoryChart'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR); ?></script>
                    <details class="dashboard-data-table">
                        <summary>View category data</summary>
                        <table>
                            <thead><tr><th>Category</th><th>Guides</th><th>Knowledge</th></tr></thead>
                            <tbody>
                            <?php foreach ($dashboard['categoryChart']['labels'] as $index => $label): ?>
                                <tr><td><?php echo e($label); ?></td><td><?php echo (int) $dashboard['categoryChart']['guides'][$index]; ?></td><td><?php echo (int) $dashboard['categoryChart']['articles'][$index]; ?></td></tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </details>
                </section>

                <section class="dashboard-panel" aria-labelledby="registration-chart-title">
                    <div class="dashboard-panel-header">
                        <div>
                            <p class="dashboard-kicker">Six-month trend</p>
                            <h2 id="registration-chart-title">User registrations</h2>
                        </div>
                    </div>
                    <div class="dashboard-chart-frame">
                        <canvas data-dashboard-chart="registrations" role="img" aria-label="User registrations during the last six months"></canvas>
                    </div>
                    <script type="application/json" data-dashboard-chart-data="registrations"><?php echo json_encode($dashboard['registrationChart'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR); ?></script>
                    <details class="dashboard-data-table">
                        <summary>View registration data</summary>
                        <table>
                            <thead><tr><th>Month</th><th>Registrations</th></tr></thead>
                            <tbody>
                            <?php foreach ($dashboard['registrationChart']['labels'] as $index => $label): ?>
                                <tr><td><?php echo e($label); ?></td><td><?php echo (int) $dashboard['registrationChart']['values'][$index]; ?></td></tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </details>
                </section>
            </div>

            <div class="dashboard-activity-grid">
                <section class="dashboard-panel" aria-labelledby="recent-guides-title">
                    <div class="dashboard-panel-header"><h2 id="recent-guides-title">Recently published guides</h2><a href="<?php echo e(application_url('guides.php')); ?>">View guides</a></div>
                    <div class="dashboard-list">
                    <?php foreach ($dashboard['recentGuides'] as $guide): ?>
                        <a class="dashboard-list-item" href="<?php echo e(application_url('guide.php?slug=' . rawurlencode($guide['slug']))); ?>"><strong><?php echo e($guide['title']); ?></strong><time datetime="<?php echo e($guide['created_at']); ?>"><?php echo e(date('d M Y', strtotime($guide['created_at']))); ?></time></a>
                    <?php endforeach; ?>
                    <?php if ($dashboard['recentGuides'] === []): ?><p class="dashboard-empty">No published guides yet.</p><?php endif; ?>
                    </div>
                </section>

                <section class="dashboard-panel" aria-labelledby="recent-posts-title">
                    <div class="dashboard-panel-header"><h2 id="recent-posts-title">Recent community posts</h2><a href="<?php echo e(application_url('community.php')); ?>">View community</a></div>
                    <div class="dashboard-list">
                    <?php foreach ($dashboard['recentPosts'] as $post): ?>
                        <div class="dashboard-list-item"><div><strong><?php echo e($post['title']); ?></strong><p>By <?php echo e($post['full_name']); ?></p></div><time datetime="<?php echo e($post['created_at']); ?>"><?php echo e(date('d M Y', strtotime($post['created_at']))); ?></time></div>
                    <?php endforeach; ?>
                    <?php if ($dashboard['recentPosts'] === []): ?><p class="dashboard-empty">No published community posts yet.</p><?php endif; ?>
                    </div>
                </section>

                <?php if ($role === 'admin'): ?>
                    <section class="dashboard-panel" aria-labelledby="recent-users-title">
                        <div class="dashboard-panel-header"><h2 id="recent-users-title">Recently registered users</h2><a href="<?php echo e(application_url('admin_users.php')); ?>">Manage users</a></div>
                        <div class="dashboard-list">
                        <?php foreach ($dashboard['recentUsers'] as $user): ?>
                            <div class="dashboard-list-item"><strong><?php echo e($user['full_name']); ?></strong><span class="dashboard-badge"><?php echo e(ucfirst($user['role'])); ?></span></div>
                        <?php endforeach; ?>
                        <?php if ($dashboard['recentUsers'] === []): ?><p class="dashboard-empty">No active users found.</p><?php endif; ?>
                        </div>
                    </section>

                    <section class="dashboard-panel" aria-labelledby="audit-title">
                        <div class="dashboard-panel-header"><h2 id="audit-title">Administrative changes</h2></div>
                        <div class="dashboard-list">
                        <?php foreach ($dashboard['auditEvents'] as $event): ?>
                            <div class="dashboard-list-item"><div><strong><?php echo e($event['action']); ?></strong><p><?php echo e($event['target_type'] . ' #' . $event['target_id']); ?></p></div><time datetime="<?php echo e($event['created_at']); ?>"><?php echo e(date('d M Y', strtotime($event['created_at']))); ?></time></div>
                        <?php endforeach; ?>
                        <?php if ($dashboard['auditEvents'] === []): ?><p class="dashboard-empty">No administrative changes recorded.</p><?php endif; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <section class="dashboard-panel dashboard-personal-activity" aria-labelledby="personal-activity-title">
                <div class="dashboard-panel-header"><h2 id="personal-activity-title">Recent activity</h2><a href="<?php echo e(application_url('guides.php')); ?>">Find a guide</a></div>
                <div class="dashboard-list">
                <?php foreach ($dashboard['activity'] as $activity): ?>
                    <div class="dashboard-list-item"><div><strong><?php echo e($activity['label']); ?></strong><p><?php echo e($activity['detail']); ?></p></div><time datetime="<?php echo e($activity['date']); ?>"><?php echo e(date('d M Y', strtotime($activity['date']))); ?></time></div>
                <?php endforeach; ?>
                <?php if ($dashboard['activity'] === []): ?><p class="dashboard-empty">Your recent support activity will appear here.</p><?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</section>
