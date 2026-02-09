<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209002044 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande_emprunt ADD CONSTRAINT FK_6B7CA7825F06C53 FOREIGN KEY (adherent_id) REFERENCES adherent (id)');
        $this->addSql('ALTER TABLE demande_emprunt ADD CONSTRAINT FK_6B7CA78C33F7837 FOREIGN KEY (document_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE demande_emprunt ADD CONSTRAINT FK_6B7CA78C5B7CBAD FOREIGN KEY (bibliothecaire_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE demande_emprunt ADD CONSTRAINT FK_6B7CA78B644C44F FOREIGN KEY (emprunt_cree_id) REFERENCES emprunt (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE emprunt ADD CONSTRAINT FK_364071D725F06C53 FOREIGN KEY (adherent_id) REFERENCES adherent (id)');
        $this->addSql('ALTER TABLE emprunt ADD CONSTRAINT FK_364071D7C33F7837 FOREIGN KEY (document_id) REFERENCES document (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande_emprunt DROP FOREIGN KEY FK_6B7CA7825F06C53');
        $this->addSql('ALTER TABLE demande_emprunt DROP FOREIGN KEY FK_6B7CA78C33F7837');
        $this->addSql('ALTER TABLE demande_emprunt DROP FOREIGN KEY FK_6B7CA78C5B7CBAD');
        $this->addSql('ALTER TABLE demande_emprunt DROP FOREIGN KEY FK_6B7CA78B644C44F');
        $this->addSql('ALTER TABLE emprunt DROP FOREIGN KEY FK_364071D725F06C53');
        $this->addSql('ALTER TABLE emprunt DROP FOREIGN KEY FK_364071D7C33F7837');
    }
}
