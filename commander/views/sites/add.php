<div class="card" style="max-width: 600px; margin: 0 auto;">
    <h3>Connect New Agent</h3>
    <p style="color: var(--color-text-muted); margin-bottom: 2rem;">
        Manually register a site here. Ideally, sites should auto-register via the Agent Plugin handshake.
    </p>

    <form method="POST" action="/sites/add">
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: var(--color-text-muted);">Site URL</label>
            <input type="url" name="url" placeholder="https://example.com" required 
                   style="width: 100%; padding: 0.75rem; background-color: var(--color-bg-dark); border: 1px solid var(--color-bg-hover); border-radius: var(--radius-md); color: var(--color-text-main);">
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: var(--color-text-muted);">Public Key (Optional)</label>
            <textarea name="public_key" rows="5" placeholder="--- BEGIN PUBLIC KEY ---"
                      style="width: 100%; padding: 0.75rem; background-color: var(--color-bg-dark); border: 1px solid var(--color-bg-hover); border-radius: var(--radius-md); color: var(--color-text-main); font-family: monospace;"></textarea>
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" style="flex: 1; padding: 0.875rem; background-color: var(--color-primary); color: white; border: none; border-radius: var(--radius-md); font-weight: 600; cursor: pointer;">Register Site</button>
            <a href="/sites" style="padding: 0.875rem; color: var(--color-text-muted); text-decoration: none;">Cancel</a>
        </div>
    </form>
</div>
