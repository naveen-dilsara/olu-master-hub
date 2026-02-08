<div class="card" style="max-width: 600px; margin: 0 auto;">
    <h3>🌍 Global Agent Configuration</h3>
    <p style="color: var(--color-text-muted); margin-bottom: 2rem;">
        These settings apply to <strong>ALL connected sites</strong>. Updating this will push the new configuration to every active agent immediately.
    </p>

    <form method="POST" action="/settings/update">
         <label style="display:block; margin-bottom:0.5rem; color:var(--color-text-muted);">GPL Plugin Auto-Update Frequency</label>
         <select name="update_interval" style="width: 100%; padding: 0.75rem; background-color: var(--color-bg-dark); border: 1px solid var(--color-bg-hover); border-radius: var(--radius-md); color: var(--color-text-main); margin-bottom: 2rem;">
             <?php 
             $options = [
                 60 => 'Every 1 Minute (High Load)',
                 300 => 'Every 5 Minutes (Recommended)',
                 1800 => 'Every 30 Minutes',
                 3600 => 'Every 1 Hour',
                 21600 => 'Every 6 Hours',
                 86400 => 'Every 1 Day'
             ];
             foreach($options as $val => $label): 
                 $selected = ($current_interval == $val) ? 'selected' : '';
             ?>
                 <option value="<?= $val ?>" <?= $selected ?>><?= $label ?></option>
             <?php endforeach; ?>
         </select>
         
         <button type="submit" class="btn-primary">
             Save & Sync All Agents
         </button>
    </form>
</div>
