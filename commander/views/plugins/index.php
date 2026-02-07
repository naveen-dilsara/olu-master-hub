<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h3>Repository Inventory</h3>
        <a href="/plugins/upload" style="background-color: var(--color-primary); color: white; padding: 0.5rem 1rem; border-radius: var(--radius-md); text-decoration: none; font-size: 0.875rem;">+ Upload Plugin</a>
    </div>

    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 1px solid var(--color-bg-hover);">
                <th style="padding: 1rem; color: var(--color-text-muted); font-weight: 500;">Plugin Name</th>
                <th style="padding: 1rem; color: var(--color-text-muted); font-weight: 500;">Slug</th>
                <th style="padding: 1rem; color: var(--color-text-muted); font-weight: 500;">Version</th>
                <th style="padding: 1rem; color: var(--color-text-muted); font-weight: 500;">Last Updated</th>
                <th style="padding: 1rem; color: var(--color-text-muted); font-weight: 500;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($plugins)): ?>
            <tr>
                <td colspan="5" style="padding: 2rem; text-align: center; color: var(--color-text-muted);">
                    Repository is empty. Upload a GPL plugin to begin.
                </td>
            </tr>
            <?php else: ?>
                <?php foreach ($plugins as $plugin): ?>
                <tr style="border-bottom: 1px solid var(--color-bg-hover);">
                    <td style="padding: 1rem; font-weight: 500; color: var(--color-text-main);">
                        <?= htmlspecialchars($plugin['name']) ?>
                    </td>
                    <td style="padding: 1rem; color: var(--color-text-muted); font-family: monospace;">
                        <?= htmlspecialchars($plugin['slug']) ?>
                    </td>
                    <td style="padding: 1rem;">
                        <span class="status-badge status-success"><?= htmlspecialchars($plugin['version']) ?></span>
                    </td>
                    <td style="padding: 1rem;"><?= $plugin['updated_at'] ?></td>
                    <td style="padding: 1rem;">
                        <button style="background: none; border: 1px solid var(--color-bg-hover); color: var(--color-text-muted); padding: 0.25rem 0.5rem; border-radius: 0.25rem; cursor: pointer;">Deploy</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
