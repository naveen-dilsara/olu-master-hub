<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
    <!-- Stats Cards -->
    <div class="card">
        <h3>Total Sites</h3>
        <div style="font-size: 2.5rem; font-weight: 700; color: var(--color-primary);"><?= $stats['total_sites'] ?></div>
        <div style="color: var(--color-text-muted); font-size: 0.875rem;">Connected Agents</div>
    </div>

    <div class="card">
        <h3>Repository</h3>
        <div style="font-size: 2.5rem; font-weight: 700; color: var(--color-accent);"><?= $stats['premium_plugins'] ?></div>
        <div style="color: var(--color-text-muted); font-size: 0.875rem;">Premium Plugins Available</div>
    </div>

    <div class="card">
        <h3>System Health</h3>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <div style="width: 12px; height: 12px; border-radius: 50%; background-color: var(--color-success); box-shadow: 0 0 10px var(--color-success);"></div>
            <span style="font-weight: 600; color: var(--color-text-main);"><?= $stats['system_status'] ?></span>
        </div>
        <div style="color: var(--color-text-muted); font-size: 0.875rem; margin-top: 0.5rem;">Last checked: Just now</div>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <h3>Recent Activity (New Connections)</h3>
    <?php if (empty($activity)): ?>
        <div style="padding: 2rem; text-align: center; color: var(--color-text-muted); border: 1px dashed var(--color-bg-hover); border-radius: var(--radius-md);">
            No recent updates or dispatcher events.
        </div>
    <?php else: ?>
        <ul style="list-style: none; padding: 0;">
            <?php foreach ($activity as $site): ?>
            <li style="padding: 1rem; border-bottom: 1px solid var(--color-bg-hover); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong style="color: var(--color-text-main);"><?= htmlspecialchars($site['url']) ?></strong>
                    <div style="font-size: 0.8em; color: var(--color-text-muted);">Connected at <?= $site['created_at'] ?></div>
                </div>
                <span class="status-badge status-success">User ID: <?= $site['id'] ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
