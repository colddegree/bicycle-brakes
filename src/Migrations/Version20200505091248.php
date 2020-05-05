<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200505091248 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Добавить каскадное удаление возможных и нормальных значений признака при удалении признака';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE feature_possible_value DROP FOREIGN KEY feature_possible_value_ibfk_1');
        $this->addSql(<<<'SQL'
ALTER TABLE feature_possible_value
ADD CONSTRAINT feature_possible_value_ibfk_1
FOREIGN KEY (feature_id) REFERENCES feature (id)
ON DELETE CASCADE
SQL);

        $this->addSql('ALTER TABLE feature_normal_value DROP FOREIGN KEY feature_normal_value_ibfk_1');
        $this->addSql(<<<'SQL'
ALTER TABLE feature_normal_value
ADD CONSTRAINT feature_normal_value_ibfk_1
FOREIGN KEY (feature_id) REFERENCES feature (id)
ON DELETE CASCADE
SQL);
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE feature_possible_value DROP FOREIGN KEY feature_possible_value_ibfk_1');
        $this->addSql(<<<'SQL'
ALTER TABLE feature_possible_value
ADD CONSTRAINT feature_possible_value_ibfk_1
FOREIGN KEY (feature_id) REFERENCES feature (id)
SQL);

        $this->addSql('ALTER TABLE feature_normal_value DROP FOREIGN KEY feature_normal_value_ibfk_1');
        $this->addSql(<<<'SQL'
ALTER TABLE feature_normal_value
ADD CONSTRAINT feature_normal_value_ibfk_1
FOREIGN KEY (feature_id) REFERENCES feature (id)
SQL);
    }
}
