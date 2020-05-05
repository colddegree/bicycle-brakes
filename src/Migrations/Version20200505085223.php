<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200505085223 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Добавить каскадное удаление значений признаков неисправностей при удалении признака или неисправности';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE malfunction_feature_value_bind DROP FOREIGN KEY FK_A5470F5018229D0A');
        $this->addSql('ALTER TABLE malfunction_feature_value_bind DROP FOREIGN KEY FK_A5470F5060E4B879');

        $this->addSql(<<<'SQL'
ALTER TABLE malfunction_feature_value_bind
ADD CONSTRAINT FK_A5470F5018229D0A
FOREIGN KEY (malfunction_id) REFERENCES malfunction (id)
ON DELETE CASCADE
SQL);
        $this->addSql(<<<'SQL'
ALTER TABLE malfunction_feature_value_bind
ADD CONSTRAINT FK_A5470F5060E4B879
FOREIGN KEY (feature_id) REFERENCES feature (id)
ON DELETE CASCADE
SQL);
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE malfunction_feature_value_bind DROP FOREIGN KEY FK_A5470F5018229D0A');
        $this->addSql('ALTER TABLE malfunction_feature_value_bind DROP FOREIGN KEY FK_A5470F5060E4B879');

        $this->addSql(<<<'SQL'
ALTER TABLE malfunction_feature_value_bind
ADD CONSTRAINT FK_A5470F5018229D0A
FOREIGN KEY (malfunction_id) REFERENCES malfunction (id)
SQL);
        $this->addSql(<<<'SQL'
ALTER TABLE malfunction_feature_value_bind
ADD CONSTRAINT FK_A5470F5060E4B879
FOREIGN KEY (feature_id) REFERENCES feature (id)
SQL);
    }
}
