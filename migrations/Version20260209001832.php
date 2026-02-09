<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209001832 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE demande_emprunt (id INT AUTO_INCREMENT NOT NULL, date_demande DATETIME NOT NULL, date_emprunt_souhaitee DATE DEFAULT NULL, duree_souhaitee_jours INT DEFAULT NULL, message_adherent LONGTEXT DEFAULT NULL, statut VARCHAR(50) NOT NULL, motif_refus LONGTEXT DEFAULT NULL, date_traitement DATETIME DEFAULT NULL, adherent_id INT NOT NULL, document_id INT NOT NULL, bibliothecaire_id INT NOT NULL, emprunt_cree_id INT DEFAULT NULL, INDEX IDX_6B7CA7825F06C53 (adherent_id), INDEX IDX_6B7CA78C33F7837 (document_id), INDEX IDX_6B7CA78C5B7CBAD (bibliothecaire_id), INDEX IDX_6B7CA78B644C44F (emprunt_cree_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE demande_emprunt ADD CONSTRAINT FK_6B7CA7825F06C53 FOREIGN KEY (adherent_id) REFERENCES adherent (id)');
        $this->addSql('ALTER TABLE demande_emprunt ADD CONSTRAINT FK_6B7CA78C33F7837 FOREIGN KEY (document_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE demande_emprunt ADD CONSTRAINT FK_6B7CA78C5B7CBAD FOREIGN KEY (bibliothecaire_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE demande_emprunt ADD CONSTRAINT FK_6B7CA78B644C44F FOREIGN KEY (emprunt_cree_id) REFERENCES emprunt (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX idx_adherent_email ON adherent (email)');
        $this->addSql('CREATE INDEX idx_adherent_nom ON adherent (nom)');
        $this->addSql('CREATE INDEX idx_document_titre ON document (titre)');
        $this->addSql('CREATE INDEX idx_document_type ON document (type)');
        $this->addSql('CREATE INDEX idx_document_disponible ON document (disponible)');
        $this->addSql('ALTER TABLE emprunt ADD CONSTRAINT FK_364071D725F06C53 FOREIGN KEY (adherent_id) REFERENCES adherent (id)');
        $this->addSql('ALTER TABLE emprunt ADD CONSTRAINT FK_364071D7C33F7837 FOREIGN KEY (document_id) REFERENCES document (id)');
        $this->addSql('CREATE INDEX idx_emprunt_statut ON emprunt (statut)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande_emprunt DROP FOREIGN KEY FK_6B7CA7825F06C53');
        $this->addSql('ALTER TABLE demande_emprunt DROP FOREIGN KEY FK_6B7CA78C33F7837');
        $this->addSql('ALTER TABLE demande_emprunt DROP FOREIGN KEY FK_6B7CA78C5B7CBAD');
        $this->addSql('ALTER TABLE demande_emprunt DROP FOREIGN KEY FK_6B7CA78B644C44F');
        $this->addSql('DROP TABLE demande_emprunt');
        $this->addSql('DROP INDEX idx_adherent_email ON adherent');
        $this->addSql('DROP INDEX idx_adherent_nom ON adherent');
        $this->addSql('DROP INDEX idx_document_titre ON document');
        $this->addSql('DROP INDEX idx_document_type ON document');
        $this->addSql('DROP INDEX idx_document_disponible ON document');
        $this->addSql('ALTER TABLE emprunt DROP FOREIGN KEY FK_364071D725F06C53');
        $this->addSql('ALTER TABLE emprunt DROP FOREIGN KEY FK_364071D7C33F7837');
        $this->addSql('DROP INDEX idx_emprunt_statut ON emprunt');
    }
}
