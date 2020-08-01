<?php

if (OC_ENV_PROD) {
    Sentry\init(
        [
            'dsn' => 'https://' . SENTRY_PROJECT_KEY . '@o443919.ingest.sentry.io/' . SENTRY_ID,
            'release' => PROJECT_NAME . '@' . PROJECT_VERSION,
        ]
    );
}
