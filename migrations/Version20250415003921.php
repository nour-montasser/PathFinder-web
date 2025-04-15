<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to fix primary key issues including the languages table.
 */
final class Version20250415003921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration to fix primary key issues in app_user and related entities, including the languages table';
    }

    public function up(Schema $schema): void
    {
        // Create app_user table
        $this->addSql('
            CREATE TABLE app_user (
                id_user BIGINT AUTO_INCREMENT NOT NULL,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                password VARCHAR(255) NOT NULL,
                role BIGINT NOT NULL,
                image VARCHAR(255) NOT NULL,
                PRIMARY KEY(id_user)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        // Create cv table with the correct primary key
        $this->addSql('
            CREATE TABLE cv (
                id_cv BIGINT AUTO_INCREMENT NOT NULL,
                id_user BIGINT DEFAULT NULL,
                title VARCHAR(255) NOT NULL,
                user_title VARCHAR(255) NOT NULL,
                introduction VARCHAR(500) NOT NULL,
                date_creation DATETIME NOT NULL,
                skills VARCHAR(255) NOT NULL,
                last_viewed DATETIME NOT NULL,
                favorite TINYINT(1) NOT NULL,
                PRIMARY KEY(id_cv),
                INDEX IDX_B66FFE926B3CA4B (id_user),
                FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        // Create certificates table with the correct primary key and foreign key
        $this->addSql('
            CREATE TABLE certificates (
                id_certificate BIGINT AUTO_INCREMENT NOT NULL,
                id_cv BIGINT DEFAULT NULL,
                title VARCHAR(255) NOT NULL,
                description VARCHAR(500) NOT NULL,
                media VARCHAR(255) NOT NULL,
                issue_date DATE NOT NULL,
                issued_by VARCHAR(255) NOT NULL,
                PRIMARY KEY(id_certificate),
                INDEX IDX_8D26FB5F76120795 (id_cv),
                FOREIGN KEY (id_cv) REFERENCES cv (id_cv) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        // Create experience table with the correct primary key and foreign key
        $this->addSql('
            CREATE TABLE experience (
                id_experience BIGINT AUTO_INCREMENT NOT NULL,
                id_cv BIGINT DEFAULT NULL,
                type VARCHAR(255) NOT NULL,
                position VARCHAR(255) NOT NULL,
                location_name VARCHAR(255) NOT NULL,
                start_date DATETIME NOT NULL,
                end_date DATETIME NOT NULL,
                description VARCHAR(500) NOT NULL,
                PRIMARY KEY(id_experience),
                INDEX IDX_590C10376120795 (id_cv),
                FOREIGN KEY (id_cv) REFERENCES cv (id_cv) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        // Create job_offer table with the correct primary key and foreign key
        $this->addSql('
            CREATE TABLE job_offer (
                id_offer BIGINT AUTO_INCREMENT NOT NULL,
                id_user BIGINT DEFAULT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                date_posted DATETIME NOT NULL,
                type VARCHAR(255) NOT NULL,
                number_of_spots INT NOT NULL,
                required_education VARCHAR(255) NOT NULL,
                required_experience VARCHAR(255) NOT NULL,
                skills VARCHAR(255) NOT NULL,
                field VARCHAR(255) NOT NULL,
                address VARCHAR(255) NOT NULL,
                PRIMARY KEY(id_offer),
                INDEX IDX_288A3A4E6B3CA4B (id_user),
                FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        // Create application_job table with the correct primary key and foreign key
        $this->addSql('
            CREATE TABLE application_job (
                application_id BIGINT NOT NULL,
                job_offer_id BIGINT DEFAULT NULL,
                id_user BIGINT DEFAULT NULL,
                cv_id BIGINT DEFAULT NULL,
                date_application DATETIME NOT NULL,
                status VARCHAR(50) NOT NULL,
                PRIMARY KEY(application_id),
                INDEX IDX_75E7356C3481D195 (job_offer_id),
                INDEX IDX_75E7356C6B3CA4B (id_user),
                INDEX IDX_75E7356CCFE419E2 (cv_id),
                FOREIGN KEY (job_offer_id) REFERENCES job_offer (id_offer),
                FOREIGN KEY (id_user) REFERENCES app_user (id_user),
                FOREIGN KEY (cv_id) REFERENCES cv (id_cv)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        // Create languages table with the correct primary key and foreign key
        $this->addSql('
            CREATE TABLE languages (
                id_language BIGINT AUTO_INCREMENT NOT NULL,
                id_cv BIGINT DEFAULT NULL,
                language_name VARCHAR(255) NOT NULL,
                level VARCHAR(255) NOT NULL,
                PRIMARY KEY(id_language),
                INDEX IDX_A0D1537976120795 (id_cv),
                FOREIGN KEY (id_cv) REFERENCES cv (id_cv) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema): void
    {
        // Drop tables in reverse order
        $this->addSql('DROP TABLE application_job');
        $this->addSql('DROP TABLE job_offer');
        $this->addSql('DROP TABLE experience');
        $this->addSql('DROP TABLE certificates');
        $this->addSql('DROP TABLE languages');
        $this->addSql('DROP TABLE cv');
        $this->addSql('DROP TABLE app_user');
    }
}
