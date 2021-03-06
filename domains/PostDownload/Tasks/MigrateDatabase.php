<?php

namespace Domains\PostDownload\Tasks;

use Domains\PostDownload\PostDownloadTaskGroup;

class MigrateDatabase implements PostDownloadTaskGroup
{
    public function __construct(private string $artisan)
    {
    }

    public function title(): string
    {
        return 'Migrate the database';
    }

    public function tasks(): array
    {
        return ["$this->artisan migrate"];
    }
}
