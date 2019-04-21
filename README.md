#Transactional email microservice - code challenge
Description
To streamline all transactional emails within Takeaway we want a transactional email
microservice. This service should make sure transactional emails are sent with a high degree of
certainty.
This microservice will use external services to actually sent the emails.
When such an external service is unavailable there should be a fallback to a secondary service.
In the future, there probably will be more fallbacks so this should be taken into account.
You're free to use any email delivery platforms but you should use at least two (one as a
fallback). E.g. Mailjet and Sendgrid are both offering free accounts and have great API's.
Do not use the Laravel mailer but write your own implementation.
At first, this microservice should be able to send an e-mail by an (JSON) API and through a CLI
command. We also want to have a log entry for every email that is sent through this
microservice.
To improve the speed of the API calls the sending should happen asynchronously (use the
queuing technique of your own preference).
Make sure;
- The micro-service is horizontal scalable
- The code has tests
- You’re including a readme which describes the choices you made and why
- You’re using micro-commits
Important: we'll only have Docker running on our machines and should not have to install any
additional software to get this micro-service running.
Bonus points
- Create a VueJS application
  - which allows us to send an email (using this service)
  - which allows us to see all the emails with their status (e.g. queued, bounced,
delivered)
- Allow multiple mail formats
  - HTML
  - Markdown
  - Text
- Allow more recipients

Out of scope
Don't worry about the authentication/authorization for this microservice for now.
##Required techniques:
- Docker
- PHP
- MySQL (or any other data storage)
Preferred techniques:
- Laravel/Lumen
- VueJS
Done?
Push all the micro-commits to a Github repository and share this with one of our recruiters and
include the repositories url.

## Project setup
- Build & Run (docker-compose up --build -d)
- Move to www directory and install required packages(composer install)
- Move to www directory and run: php artisan migrate:fresh && php artisan queue:work --tries=6 (There are set 2 Email Providers, second as fallback, for each provider there are 3 tries, in total 6 tries)
- For sending email from console use command php artisan email:send with specified arguments.
- For sending email through api use post request to endpoind /email with specified arguments.
