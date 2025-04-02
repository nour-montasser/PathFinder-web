<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250402032252 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Modify primary keys to AUTO_INCREMENT and handle foreign key constraints';
    }

    public function up(Schema $schema): void
    {
        // Drop foreign key constraints referencing skilltest.id_test
        $this->addSql('ALTER TABLE test_result DROP FOREIGN KEY FK_84B3C63D535F620E');
        $this->addSql('ALTER TABLE questions DROP FOREIGN KEY FK_8ADC54D5535F620E');

        // Drop foreign key constraints referencing app_user.id_user
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F77846360CB89');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F77847A0FAE8A');
        $this->addSql('ALTER TABLE serviceoffre DROP FOREIGN KEY FK_SERVICE_USER_NEW');
        $this->addSql('ALTER TABLE test_result DROP FOREIGN KEY FK_84B3C63D6B3CA4B');

        // Modify columns
        $this->addSql('ALTER TABLE app_user CHANGE id_user id_user BIGINT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE report CHANGE id_report id_report BIGINT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE skilltest CHANGE id_test id_test BIGINT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE test_result CHANGE id_result id_result BIGINT AUTO_INCREMENT NOT NULL');

        // Recreate foreign key constraints
        $this->addSql('ALTER TABLE test_result ADD CONSTRAINT FK_84B3C63D535F620E FOREIGN KEY (id_test) REFERENCES skilltest (id_test)');
        $this->addSql('ALTER TABLE questions ADD CONSTRAINT FK_8ADC54D5535F620E FOREIGN KEY (id_test) REFERENCES skilltest (id_test)');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77846360CB89 FOREIGN KEY (id_user_target) REFERENCES app_user (id_user)');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77847A0FAE8A FOREIGN KEY (id_user_sender) REFERENCES app_user (id_user)');
        $this->addSql('ALTER TABLE serviceoffre ADD CONSTRAINT FK_SERVICE_USER_NEW FOREIGN KEY (id_user) REFERENCES app_user (id_user)');
        $this->addSql('ALTER TABLE test_result ADD CONSTRAINT FK_84B3C63D6B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user)');
    }

    public function down(Schema $schema): void
    {
        // Drop foreign key constraints
        $this->addSql('ALTER TABLE test_result DROP FOREIGN KEY FK_84B3C63D535F620E');
        $this->addSql('ALTER TABLE questions DROP FOREIGN KEY FK_8ADC54D5535F620E');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F77846360CB89');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F77847A0FAE8A');
        $this->addSql('ALTER TABLE serviceoffre DROP FOREIGN KEY FK_SERVICE_USER_NEW');
        $this->addSql('ALTER TABLE test_result DROP FOREIGN KEY FK_84B3C63D6B3CA4B');

        // Revert column changes
        $this->addSql('ALTER TABLE app_user CHANGE id_user id_user BIGINT NOT NULL');
        $this->addSql('ALTER TABLE report CHANGE id_report id_report BIGINT NOT NULL');
        $this->addSql('ALTER TABLE skilltest CHANGE id_test id_test BIGINT NOT NULL');
        $this->addSql('ALTER TABLE test_result CHANGE id_result id_result BIGINT NOT NULL');

        // Recreate foreign key constraints
        $this->addSql('ALTER TABLE test_result ADD CONSTRAINT FK_84B3C63D535F620E FOREIGN KEY (id_test) REFERENCES skilltest (id_test)');
        $this->addSql('ALTER TABLE questions ADD CONSTRAINT FK_8ADC54D5535F620E FOREIGN KEY (id_test) REFERENCES skilltest (id_test)');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77846360CB89 FOREIGN KEY (id_user_target) REFERENCES app_user (id_user)');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77847A0FAE8A FOREIGN KEY (id_user_sender) REFERENCES app_user (id_user)');
        $this->addSql('ALTER TABLE serviceoffre ADD CONSTRAINT FK_SERVICE_USER_NEW FOREIGN KEY (id_user) REFERENCES app_user (id_user)');
        $this->addSql('ALTER TABLE test_result ADD CONSTRAINT FK_84B3C63D6B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user)');
    }
}