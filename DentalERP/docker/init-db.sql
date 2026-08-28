-- Runs automatically by the postgres image on first initialization (fresh
-- volume only). Creates the dedicated PHPUnit database so the test suite
-- (RefreshDatabase / migrate:fresh) never wipes the app/preview database
-- "dentalerp_test".
CREATE DATABASE dentalerp_phpunit;
GRANT ALL PRIVILEGES ON DATABASE dentalerp_phpunit TO postgres;
