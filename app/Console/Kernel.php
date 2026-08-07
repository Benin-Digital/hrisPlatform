protected function schedule(Schedule $schedule)
{
    // Rappel à 8h00
    $schedule->command('pointage:rappel-matin')->dailyAt('08:00');

    // Retard à 8h30
    $schedule->command('pointage:retard')->dailyAt('08:30');

    // Absent à 18h00
    $schedule->command('pointage:absent')->dailyAt('18:00');
}