<div class="card" style="max-width: 600px; margin: 0 auto;">
    <h3>Upload to Repository</h3>
    <p style="color: var(--color-text-muted); margin-bottom: 2rem;">
        Upload a valid WordPress plugin ZIP file. It will be stored securely and ready for distribution.
    </p>

    <form method="POST" action="/plugins/upload" enctype="multipart/form-data">
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: var(--color-text-muted);">Plugin Name</label>
            <input type="text" name="name" placeholder="e.g. Elementor Pro" required 
                   style="width: 100%; padding: 0.75rem; background-color: var(--color-bg-dark); border: 1px solid var(--color-bg-hover); border-radius: var(--radius-md); color: var(--color-text-main);">
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: var(--color-text-muted);">Slug (Optional)</label>
            <input type="text" name="slug" placeholder="elementor-pro" 
                   style="width: 100%; padding: 0.75rem; background-color: var(--color-bg-dark); border: 1px solid var(--color-bg-hover); border-radius: var(--radius-md); color: var(--color-text-main);">
            <div style="font-size: 0.75rem; color: var(--color-text-muted); margin-top: 0.25rem;">Must match the folder name inside the ZIP.</div>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: var(--color-text-muted);">Version</label>
            <input type="text" name="version" placeholder="1.0.0" required 
                   style="width: 100%; padding: 0.75rem; background-color: var(--color-bg-dark); border: 1px solid var(--color-bg-hover); border-radius: var(--radius-md); color: var(--color-text-main);">
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: var(--color-text-muted);">Plugin ZIP File</label>
            <div style="border: 2px dashed var(--color-bg-hover); padding: 2rem; text-align: center; border-radius: var(--radius-lg); cursor: pointer;" onclick="document.getElementById('file-input').click()">
                <div style="margin-bottom: 0.5rem; color: var(--color-primary);">Click to Select File</div>
                <div style="font-size: 0.875rem; color: var(--color-text-muted);">.zip files only</div>
                <input type="file" id="file-input" name="plugin_zip" accept=".zip" required style="display: none;" onchange="document.getElementById('file-name').textContent = this.files[0].name">
                <div id="file-name" style="margin-top: 0.5rem; font-weight: 500; color: var(--color-text-main);"></div>
            </div>
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" style="flex: 1; padding: 0.875rem; background-color: var(--color-primary); color: white; border: none; border-radius: var(--radius-md); font-weight: 600; cursor: pointer;">Upload to Secure Storage</button>
            <a href="/plugins" style="padding: 0.875rem; color: var(--color-text-muted); text-decoration: none;">Cancel</a>
        </div>
    </form>
</div>
