/* Automating mysql_secure_install */
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.db WHERE db='test' OR db='test\_%';
FLUSH PRIVILEGES;

/* Prepare scrapesy database and setup default admin user */
CREATE DATABASE scrapesy;
USE scrapesy;

CREATE TABLE users (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_admin VARCHAR(3) NOT NULL,
    is_disabled VARCHAR(3) NOT NULL
);

/* Default Administrator user 'admin' with password 'changeme' */
INSERT INTO users (id,username,password,is_admin,is_disabled) VALUES ('1','admin','$2y$10$O5Npp.rS8l2yAUve1QWN.eTXix1l69nVcxmNnU2YJc5CxBaRleMLW','Yes','No');