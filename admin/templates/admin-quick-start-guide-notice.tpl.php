<div class="update-nag clearfix">
    <p><?php _e('Hello and welcome to <strong>Another WordPress Classifieds</strong>. This plugin is super easy to use AND highly configurable.', 'AWPCP') ?></p>
    <p><?php _e('Would you like some help getting started?', 'AWPCP') ?></p>

    <form id="quick-start-guide-notice">
        <div style="float:left;width:50%">
            <p><input type="submit" value="No Thanks" class="button" id="submit-no-thanks" name="submit"><br/>
                I'll figure it out on my own.</p>
        </div>
        <div style="float:left;width:50%">
            <p><input type="submit" value="Yes Please!" class="button-primary" id="submit-yes-please" name="submit"><br/>
                Help me get my classifieds running quickly.</p>
        </div>
        <input type="hidden" class="redirect-url" name="redirect-url" value="<?php echo esc_attr('http://awpcp.com/quick-start-guide') ?>">
    </form>
</div>