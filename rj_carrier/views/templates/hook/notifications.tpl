<div id="notifications-transport">
    {if $notifications.error}
    {block name='notifications_error'}
    <div class="alert alert-danger" role="alert" data-alert="danger">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">
                <i class="material-icons">close</i>
            </span>
        </button>
        <div class="alert-text">
            <ul>
                {foreach $notifications.error as $notif}
                <li>{$notif nofilter}</li>
                {/foreach}
            </ul>
        </div>
    </div>
    {/block}
    {/if}

    {if $notifications.warning}
    {block name='notifications_warning'}
    <div class="alert alert-warning" role="alert" data-alert="warning">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">
                <i class="material-icons">close</i>
            </span>
        </button>
        <div class="alert-text">
            <ul>
                {foreach $notifications.warning as $notif}
                <li>{$notif nofilter}</li>
                {/foreach}
            </ul>
        </div>
    </div>
    {/block}
    {/if}

    {if $notifications.success}
    {block name='notifications_success'}
    <div class="alert alert-success" role="alert" data-alert="success">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">
                <i class="material-icons">close</i>
            </span>
        </button>
        <div class="alert-text">
            <ul>
                {foreach $notifications.success as $notif}
                <li>{$notif nofilter}</li>
                {/foreach}
            </ul>
        </div>
    </div>
    {/block}
    {/if}

    {if $notifications.info}
    {block name='notifications_info'}
    <div class="alert alert-info" role="alert" data-alert="info">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">
                <i class="material-icons">close</i>
            </span>
        </button>
        <div class="alert-text">
            <ul>
                {foreach $notifications.info as $notif}
                <li>{$notif nofilter}</li>
                {/foreach}
            </ul>
        </div>
    </div>
    {/block}
    {/if}

</div>