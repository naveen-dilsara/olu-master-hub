<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h3>System Event Log</h3>
        <button onclick="location.reload()" style="background-color: var(--color-bg-hover); color: var(--color-text-main); padding: 0.5rem 1rem; border: none; border-radius: var(--radius-md); cursor: pointer; font-size: 0.875rem;">
            🔄 Refresh Logs
        </button>
    </div>

    <div style="background: #1e293b; border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--color-bg-hover);">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-family: monospace; font-size: 0.85rem;">
            <thead>
                <tr style="background: #0f172a; border-bottom: 1px solid var(--color-bg-hover);">
                    <th style="padding: 0.75rem 1rem; color: var(--color-text-muted); width: 180px;">Timestamp</th>
                    <th style="padding: 0.75rem 1rem; color: var(--color-text-muted); width: 100px;">Level</th>
                    <th style="padding: 0.75rem 1rem; color: var(--color-text-muted);">Message</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="3" style="padding: 2rem; text-align: center; color: var(--color-text-muted);">
                        No logs found.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td style="padding: 0.5rem 1rem; color: #94a3b8; white-space: nowrap;"><?= htmlspecialchars($log['timestamp']) ?></td>
                        <td style="padding: 0.5rem 1rem;">
                            <?php 
                                $badgeColor = '#64748b'; // default
                                if ($log['level'] === 'AutoUpdate') $badgeColor = '#d946ef'; // pink
                                if ($log['level'] === 'ERROR' || $log['level'] === 'FAIL') $badgeColor = '#ef4444'; // red
                                if ($log['level'] === 'WARN') $badgeColor = '#f59e0b'; // amber
                                if ($log['level'] === 'SUCCESS') $badgeColor = '#22c55e'; // green
                            ?>
                            <span style="background-color: <?= $badgeColor ?>20; color: <?= $badgeColor ?>; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">
                                <?= htmlspecialchars($log['level']) ?>
                            </span>
                        </td>
                        <td style="padding: 0.5rem 1rem; color: #cbd5e1; word-break: break-all;">
                            <?= htmlspecialchars($log['message']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
