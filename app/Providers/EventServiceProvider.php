<?php

namespace App\Providers;

use App\Listeners\AttachUsersToNewlyUploadedFile;
use App\Listeners\DeleteShareableLinks;
use App\Listeners\FolderTotalSizeSubscriber;
use App\Listeners\HandleDeletedWorkspace;
use App\Listeners\HydrateUserWithSampleDriveContents;
use Common\Auth\Events\UserCreated;
use Common\Files\Events\FileEntriesDeleted;
use Common\Files\Events\FileEntryCreated;
use Common\Notifications\SubscribeUserToNotifications;
use Common\Workspaces\Events\WorkspaceDeleted;
use Common\Workspaces\Listeners\AttachWorkspaceToUser;
use Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        FileEntryCreated::class => [AttachUsersToNewlyUploadedFile::class],
        FileEntriesDeleted::class => [DeleteShareableLinks::class],

        WorkspaceDeleted::class => [HandleDeletedWorkspace::class],

        Login::class => [AttachWorkspaceToUser::class],
        Registered::class => [AttachWorkspaceToUser::class],
    ];

    protected $subscribe = [FolderTotalSizeSubscriber::class];

    public function boot()
    {
        parent::boot();

        if (config('common.site.demo')) {
            Event::listen(
                UserCreated::class,
                HydrateUserWithSampleDriveContents::class,
            );
        }

        Event::listen(UserCreated::class, function (UserCreated $event) {
            app(SubscribeUserToNotifications::class)->execute(
                $event->user,
                null,
            );
        });
    }
}
