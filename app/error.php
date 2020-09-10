<?php

if (isset($_ENV['SENTRY_PROJECT_KEY']) && isset($_ENV['SENTRY_ID'])) {
    Sentry\init(
        [
            'dsn' => 'https://' . $_ENV['SENTRY_PROJECT_KEY'] . '@o443919.ingest.sentry.io/' . $_ENV['SENTRY_ID'],
            'release' => $_ENV['PROJECT_NAME'] . '@' . $_ENV['PROJECT_VERSION'],
        ]
    );
}
