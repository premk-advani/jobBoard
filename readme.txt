1) Clone project through following command
   git clone https://github.com/premk-advani/jobBoard.git
   cd jobBoard

2) create a database named board

3) open folder in vs code and run following commands one by one

   php artisan migrate
   php artisan db:seed --class=JobSeeder
   php artisan db:seed --class=AttributeSeeder
   php artisan db:seed --class=JobAttributeValueSeeder
   php artisan serve

4) copy paste the command in browser or in postman with get method to see the data
   http://127.0.0.1:8000/api/jobsByfilter?filter=(job_type=full-time AND (languages HAS_ANY (PHP,JavaScript))) AND (locations IS_ANY (New York,Remote)) AND attribute:years_experience>=3
   http://127.0.0.1:8000/api/jobsByService?filter=(job_type=full-time AND (languages HAS_ANY (PHP,JavaScript))) AND (locations IS_ANY (New York,Remote)) AND attribute:years_experience>=3

5) postman collection is present at root file name Board.postman_collection.json

