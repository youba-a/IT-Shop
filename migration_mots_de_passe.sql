-- À exécuter dans la base existante avant de mettre à jour PHP.
ALTER TABLE admin MODIFY mot_de_passe_hash VARCHAR(255) NOT NULL;
