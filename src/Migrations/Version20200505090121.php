<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200505090121 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Добавить каскадное удаление клинических картин при удалении признака или неисправности';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE malfunction_clinical_picture DROP FOREIGN KEY malfunction_clinical_picture_ibfk_1');
        $this->addSql('ALTER TABLE malfunction_clinical_picture DROP FOREIGN KEY malfunction_clinical_picture_ibfk_2');

        $this->addSql(<<<'SQL'
ALTER TABLE malfunction_clinical_picture
ADD CONSTRAINT malfunction_clinical_picture_ibfk_1
FOREIGN KEY (malfunction_id) REFERENCES malfunction (id)
ON DELETE CASCADE
SQL);
        $this->addSql(<<<'SQL'
ALTER TABLE malfunction_clinical_picture
ADD CONSTRAINT malfunction_clinical_picture_ibfk_2
FOREIGN KEY (feature_id) REFERENCES feature (id)
ON DELETE CASCADE
SQL);
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE malfunction_clinical_picture DROP FOREIGN KEY malfunction_clinical_picture_ibfk_1');
        $this->addSql('ALTER TABLE malfunction_clinical_picture DROP FOREIGN KEY malfunction_clinical_picture_ibfk_2');

        $this->addSql(<<<'SQL'
ALTER TABLE malfunction_clinical_picture
ADD CONSTRAINT malfunction_clinical_picture_ibfk_1
FOREIGN KEY (malfunction_id) REFERENCES malfunction (id)
SQL);
        $this->addSql(<<<'SQL'
ALTER TABLE malfunction_clinical_picture
ADD CONSTRAINT malfunction_clinical_picture_ibfk_2
FOREIGN KEY (feature_id) REFERENCES feature (id)
SQL);
    }
}
