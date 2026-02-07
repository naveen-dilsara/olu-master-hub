<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h3>Connected Agents</h3>
        <a href="/sites/add" style="background-color: var(--color-primary); color: white; padding: 0.5rem 1rem; border-radius: var(--radius-md); text-decoration: none; font-size: 0.875rem;">+ Connect Manual</a>
    </div>

    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 1px solid var(--color-bg-hover);">
                <th style="padding: 1rem; color: var(--color-text-muted); font-weight: 500;">Domain URL</th>
                <th style="padding: 1rem; color: var(--color-text-muted); font-weight: 500;">Status</th>
                <th style="padding: 1rem; color: var(--color-text-muted); font-weight: 500;">Last Check</th>
                <th style="padding: 1rem; color: var(--color-text-muted); font-weight: 500;">WP Version</th>
                <th style="padding: 1rem; color: var(--color-text-muted); font-weight: 500;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($sites)): ?>
            <tr>
                <td colspan="5" style="padding: 2rem; text-align: center; color: var(--color-text-muted);">
                    No sites connected yet. Install the Agent Plugin to begin.
                </td>
            </tr>
            <?php else: ?>
                <?php foreach ($sites as $site): ?>
                <tr style="border-bottom: 1px solid var(--color-bg-hover);">
                    <td style="padding: 1rem;">
                        <a href="<?= htmlspecialchars($site['url']) ?>" target="_blank" style="color: var(--color-accent); text-decoration: none;">
                            <?= htmlspecialchars($site['url']) ?>
                        </a>
                    </td>
                    <td style="padding: 1rem;">
                        <span class="status-badge <?= $site['status'] == 'active' ? 'status-success' : 'status-warning' ?>">
                            <?= ucfirst($site['status']) ?>
                        </span>
                    </td>
                    <td style="padding: 1rem;"><?= $site['last_heartbeat'] ?? 'Never' ?></td>
                    <td style="padding: 1rem;"><?= $site['wp_version'] ?? 'Unknown' ?></td>
                    <td style="padding: 1rem;">
                        <?php if($site['status'] === 'pending'): ?>
                        <a href="/sites/activate?id=<?= $site['id'] ?>" style="background-color: var(--color-success); color: white; padding: 0.25rem 0.5rem; text-decoration: none; border-radius: 0.25rem; font-size: 0.75rem;">Activate</a>
                        <?php endif; ?>
                        <a href="/sites/manage?id=<?= $site['id'] ?>" style="background: none; border: 1px solid var(--color-bg-hover); color: var(--color-text-muted); padding: 0.25rem 0.5rem; border-radius: 0.25rem; cursor: pointer; text-decoration: none; font-size: 0.85rem;">Manage</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
