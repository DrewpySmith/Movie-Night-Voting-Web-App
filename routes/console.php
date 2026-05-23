<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('rooms:check-winners')->everyMinute();

Schedule::command('model:prune')->daily();
