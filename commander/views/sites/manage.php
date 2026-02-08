<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <!-- Main Column -->
    <div>
        <div class="card">
            <div style="display: flex; justify-content: space-between;">
                <h3>Site Details</h3>
                <span class="status-badge <?= $site['status'] == 'active' ? 'status-success' : 'status-warning' ?>">
                    <?= ucfirst($site['status']) ?>
                </span>
            </div>
            <div style="margin-top: 1rem; color: var(--color-text-muted);">
                <div style="margin-bottom: 0.5rem;"><strong>URL:</strong> <a href="<?= htmlspecialchars($site['url']) ?>" target="_blank" style="color: var(--color-accent);"><?= htmlspecialchars($site['url']) ?></a></div>
                <div style="margin-bottom: 0.5rem;"><strong>WP Version:</strong> <?= htmlspecialchars($site['wp_version'] ?? 'Unknown') ?></div>
                <div style="margin-bottom: 0.5rem;"><strong>Last Heartbeat:</strong> <?= htmlspecialchars($site['last_heartbeat'] ?? 'Never') ?></div>
                <div style="margin-bottom: 0.5rem;"><strong>Public Key:</strong> 
                    <div style="background: rgba(0,0,0,0.3); padding: 0.5rem; border-radius: var(--radius-md); font-family: monospace; font-size: 0.75rem; word-break: break-all; margin-top: 0.25rem;">
                        <?= htmlspecialchars(substr($site['public_key'], 0, 50)) ?>...
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <h3>Remote Dispatcher</h3>
            <p style="color: var(--color-text-muted); margin-bottom: 1.5rem;">
                Push a plugin from the GPL Repository to this site. The agent will silently install/update it.
            </p>
            
            <form method="POST" action="/sites/dispatch">
                <input type="hidden" name="site_id" value="<?= $site['id'] ?>">
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: var(--color-text-muted);">Select Plugin</label>
                    <select name="plugin_slug" required style="width: 100%; padding: 0.75rem; background-color: var(--color-bg-dark); border: 1px solid var(--color-bg-hover); border-radius: var(--radius-md); color: var(--color-text-main);">
                        <option value="">-- Choose Plugin --</option>
                        <?php foreach ($repo_plugins as $p): ?>
                        <option value="<?= $p['slug'] ?>"><?= htmlspecialchars($p['name']) ?> (v<?= $p['version'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" style="width: 100%; padding: 0.875rem; background: linear-gradient(135deg, var(--color-primary), var(--color-accent)); color: white; border: none; border-radius: var(--radius-md); font-weight: 600; cursor: pointer;">
                    🚀 Dispatch Update
                </button>
            </form>
        </div>
    </div>

    <!-- Sidebar Column -->
    <div>
        <div class="card">
            <h3>Installed Plugins (<?= count($installed_plugins) ?>)</h3>
            <div style="max-height: 400px; overflow-y: auto;">
                <?php if (empty($installed_plugins)): ?>
                    <p style="color: var(--color-text-muted); padding: 1rem; text-align: center;">No plugins detected yet.</p>
                <?php else: ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach ($installed_plugins as $plugin): ?>
                        <li style="padding: 0.75rem; border-bottom: 1px solid var(--color-bg-hover); display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="display: block; color: var(--color-text-main);"><?= htmlspecialchars($plugin['name']) ?></strong>
                                <small style="color: var(--color-text-muted);">v<?= htmlspecialchars($plugin['version']) ?> • <?= htmlspecialchars($plugin['slug']) ?></small>
                                <?php if (!empty($plugin['has_update'])): ?>
                                    <form method="POST" action="/sites/dispatch" style="display:inline;">
                                        <input type="hidden" name="site_id" value="<?= $site['id'] ?>">
                                        <input type="hidden" name="plugin_slug" value="<?= $plugin['slug'] ?>">
                                        <input type="hidden" name="is_standard_update" value="1">
                                        <button type="submit" style="margin-left: 0.5rem; background: var(--color-accent); color: white; border: none; border-radius: 4px; padding: 2px 6px; font-size: 0.7rem; cursor: pointer;">
                                            Update Available ⬆
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <div>
                                <?php if ($plugin['is_active']): ?>
                                    <span style="color: var(--color-success); font-size: 0.75rem;">● Active</span>
                                <?php else: ?>
                                    <span style="color: var(--color-text-muted); font-size: 0.75rem;">○ Inactive</span>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
             <h3>Actions</h3>
             <button style="width: 100%; padding: 0.75rem; background-color: var(--color-bg-hover); color: var(--color-danger); border: 1px solid var(--color-danger); border-radius: var(--radius-md); cursor: pointer; margin-bottom: 0.5rem;">
                 Unlink Site
             </button>
        </div>
    </div>
</div>
