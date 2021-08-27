<?php

if (!$this->hasConfig()) {
    $this->setConfig([
            
    ]);
}

rex_sql_table::get(rex::getTable('nv_bemails_templates'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('title', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('sender_email', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('sender_name', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('replyto_email', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('subject', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('body_html', 'text', true))
    ->ensureGlobalColumns()
    ->ensure();