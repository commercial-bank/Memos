<?php

use Illuminate\Support\Facades\Schedule;

// Exécute la commande tous les jours à minuit
Schedule::command('drafts:clean')->daily();
