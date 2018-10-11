##Sample application

Sample application that calculates max profit when buying gold.

Calculation is divided in two steps: getting data and using that data for calculations.

### Installation

Clone repository to a directory.

Run composer install to install all dependencies.

### Usage

Run php -f artisan gold:fetch to get data from provider.
Running this again will remove old data and get new set from provider. 

Run php -f artisan gold:calculate --number=5 to get top 5 dates to buy gold in the past and some other useful information.
This method uses data from first step - it will work offline.

Run php -f artisan gold:monthly {--s|since=2015-01-01} {--d|day_of_month=10} {--a|amount=1000} 
Calculate earnings/loses for following scenario: start buying gold for given amount since specific date on every specific day of the month.
This method uses data from first step - it will work offline.

Run php -f artisan gold:best_day to calculate best day of the month to buy gold.
This method uses data from first step - it will work offline.

Run phpunit tests/ to run unit tests
