<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250401114338 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE serviceoffre CHANGE id_user id_user BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE serviceoffre ADD CONSTRAINT FK_3CE042986B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_3CE042986B3CA4B ON serviceoffre (id_user)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE serviceoffre DROP FOREIGN KEY FK_3CE042986B3CA4B');
        $this->addSql('DROP INDEX IDX_3CE042986B3CA4B ON serviceoffre');
        $this->addSql('ALTER TABLE serviceoffre CHANGE id_user id_user BIGINT NOT NULL');
    }
}
