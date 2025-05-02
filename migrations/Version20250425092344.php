<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250425092344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, application_id BIGINT NOT NULL, user_id BIGINT NOT NULL, amount DOUBLE PRECISION NOT NULL, paid_at DATETIME NOT NULL, receipt_url VARCHAR(255) NOT NULL, stripe_session_id VARCHAR(255) DEFAULT NULL, INDEX IDX_6D28840D3E030ACD (application_id), INDEX IDX_6D28840DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D3E030ACD FOREIGN KEY (application_id) REFERENCES applicationservice (id_app)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id_user)');
        $this->addSql('ALTER TABLE applicationservice DROP FOREIGN KEY FK_A89D71943F0033A2');
        $this->addSql('ALTER TABLE applicationservice CHANGE rating rating INT DEFAULT NULL');
        $this->addSql('ALTER TABLE applicationservice ADD CONSTRAINT FK_A89D71943F0033A2 FOREIGN KEY (id_service) REFERENCES serviceoffre (id_service)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D3E030ACD');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DA76ED395');
        $this->addSql('DROP TABLE payment');
        $this->addSql('ALTER TABLE applicationservice DROP FOREIGN KEY FK_A89D71943F0033A2');
        $this->addSql('ALTER TABLE applicationservice CHANGE rating rating INT NOT NULL');
        $this->addSql('ALTER TABLE applicationservice ADD CONSTRAINT FK_A89D71943F0033A2 FOREIGN KEY (id_service) REFERENCES serviceoffre (id_service) ON DELETE CASCADE');
    }
}
