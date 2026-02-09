<?php

namespace App\DataFixtures;

use App\Entity\Adherent;
use App\Entity\Document;
use App\Entity\Emprunt;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // 1. ADMIN & STAFF USERS
        // ======================
        
        // Admin
        $admin = new User();
        $admin->setEmail('admin@mediatheque.fr');
        $admin->setRoles([User::ROLE_ADMIN]);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Admin123!'));
        $manager->persist($admin);

        // Bibliothécaire
        $biblio = new User();
        $biblio->setEmail('biblio@mediatheque.fr');
        $biblio->setRoles([User::ROLE_BIBLIOTHECAIRE]);
        $biblio->setPassword($this->passwordHasher->hashPassword($biblio, 'Biblio123!'));
        $manager->persist($biblio);

        // 2. ADHERENTS (Profile + User Account)
        // =====================================
        
        $adherentsData = [
            ['Jean', 'Dupont', 'jean.dupont@email.fr', '0601020304', '1 Rue de la Paix, Paris'],
            ['Sophie', 'Martin', 'sophie.martin@email.fr', '0605060708', '10 Avenue des Champs, Paris'],
            ['Lucas', 'Bernard', 'lucas.bernard@email.fr', '0609101112', '5 Bd Saint-Germain, Paris'],
            ['Emma', 'Petit', 'emma.petit@email.fr', '0613141516', '15 Rue de Rivoli, Paris'],
        ];

        $adherentEntities = [];

        foreach ($adherentsData as $data) {
            // Create Adherent Profile
            $adherent = new Adherent();
            $adherent->setPrenom($data[0]);
            $adherent->setNom($data[1]);
            $adherent->setEmail($data[2]);
            $adherent->setTelephone($data[3]);
            $adherent->setAdresse($data[4]);
            $adherent->setDateInscription(new \DateTime('-' . rand(1, 12) . ' months'));
            $adherent->setActif(true);
            $manager->persist($adherent);
            $adherentEntities[] = $adherent;

            // Create Login Account
            $user = new User();
            $user->setEmail($data[2]); // Same email
            $user->setRoles([User::ROLE_ADHERENT]);
            $password = $data[0] . '123!'; // e.g., Jean123!
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
            $manager->persist($user);
        }

        // 3. DOCUMENTS
        // ============
        
        $docsData = [
            ['1984', 'George Orwell', Document::TYPE_LIVRE, '978-2070368228', 'Le Grand Frère vous regarde...'],
            ['Le Seigneur des Anneaux', 'J.R.R. Tolkien', Document::TYPE_LIVRE, '978-2070612888', 'L\'anneau unique pour les gouverner tous.'],
            ['Inception', 'Christopher Nolan', Document::TYPE_DVD, '3333333333333', 'Un voleur s\'approprie des secrets des rêves.'],
            ['Thriller', 'Michael Jackson', Document::TYPE_CD, '4444444444444', 'L\'album le plus vendu de tous les temps.'],
            ['Tintin au Tibet', 'Hergé', Document::TYPE_BD, '978-2203001145', 'Tintin part à la recherche de Tchang.'],
            ['National Geographic', 'Collectif', Document::TYPE_MAGAZINE, '5555555555555', 'Nature, science et culture.'],
            ['Dune', 'Frank Herbert', Document::TYPE_LIVRE, '978-2266283771', 'La planète des sables.'],
            ['Interstellar', 'Christopher Nolan', Document::TYPE_DVD, '6666666666666', 'L\'amour transcende le temps et l\'espace.'],
            ['Dark Side of the Moon', 'Pink Floyd', Document::TYPE_CD, '7777777777777', 'Un chef-d\'oeuvre du rock progressif.'],
            ['Astérix le Gaulois', 'Goscinny & Uderzo', Document::TYPE_BD, '978-2012101333', 'Ils sont fous ces Romains.'],
        ];

        $docEntities = [];

        foreach ($docsData as $data) {
            $doc = new Document();
            $doc->setTitre($data[0]);
            $doc->setAuteur($data[1]);
            $doc->setType($data[2]);
            $doc->setIsbn($data[3]);
            $doc->setResume($data[4]);
            $doc->setDateAcquisition(new \DateTime('-' . rand(1, 24) . ' months'));
            // Disponibilité is true by default, will be set to false if borrowed
            $doc->setDisponible(true); 
            $manager->persist($doc);
            $docEntities[] = $doc;
        }

        $manager->flush(); // Flush intermediates if needed, but we can do at end.

        // 4. EMPRUNTS
        // ===========
        
        // Emprunt En Cours (Normal) - Jean borrows 1984
        $emprunt1 = new Emprunt();
        $emprunt1->setAdherent($adherentEntities[0]);
        $emprunt1->setDocument($docEntities[0]);
        $emprunt1->setDateEmprunt(new \DateTime('-1 week'));
        $emprunt1->setDateRetourPrevue(new \DateTime('+2 weeks'));
        $emprunt1->setStatut(Emprunt::STATUT_EN_COURS);
        // Note: Logic in Service/Subscriber usually handles this, but in Fixtures we set explicitly
        $docEntities[0]->setDisponible(false);
        $manager->persist($emprunt1);

        // Emprunt En Retard - Jean borrows Inception (Late!)
        $emprunt2 = new Emprunt();
        $emprunt2->setAdherent($adherentEntities[0]);
        $emprunt2->setDocument($docEntities[2]);
        $emprunt2->setDateEmprunt(new \DateTime('-2 months'));
        $emprunt2->setDateRetourPrevue(new \DateTime('-1 month')); // Late by 1 month
        $emprunt2->setStatut(Emprunt::STATUT_RETARD); // Technically computed, but set for query test
        $docEntities[2]->setDisponible(false);
        $manager->persist($emprunt2);

        // Emprunt Terminé - Sophie returned Tintin
        $emprunt3 = new Emprunt();
        $emprunt3->setAdherent($adherentEntities[1]);
        $emprunt3->setDocument($docEntities[4]);
        $emprunt3->setDateEmprunt(new \DateTime('-3 weeks'));
        $emprunt3->setDateRetourPrevue(new \DateTime('-1 day'));
        $emprunt3->setDateRetourEffective(new \DateTime('-2 days'));
        $emprunt3->setStatut(Emprunt::STATUT_TERMINE);
        $docEntities[4]->setDisponible(true); // Is available
        $manager->persist($emprunt3);

        // 5. DEMANDES D'EMPRUNT
        // =====================

        // Lucas demande Interstellar (En attente)
        $demande1 = new \App\Entity\DemandeEmprunt();
        $demande1->setAdherent($adherentEntities[2]); // Lucas
        $demande1->setDocument($docEntities[7]); // Interstellar
        $demande1->setBibliothecaire($biblio);
        $demande1->setDateDemande(new \DateTime('-2 days'));
        $demande1->setDateEmpruntSouhaitee(new \DateTime('+1 day'));
        $demande1->setDureeSouhaiteeJours(14);
        $demande1->setMessageAdherent("Pour le ciné-club de vendredi.");
        $demande1->setStatut(\App\Entity\DemandeEmprunt::STATUT_EN_ATTENTE);
        $manager->persist($demande1);

        // Emma demande Thriller (Acceptée)
        $demande2 = new \App\Entity\DemandeEmprunt();
        $demande2->setAdherent($adherentEntities[3]); // Emma
        $demande2->setDocument($docEntities[3]); // Thriller
        $demande2->setBibliothecaire($biblio);
        $demande2->setDateDemande(new \DateTime('-5 days'));
        $demande2->setDateEmpruntSouhaitee(new \DateTime('-4 days'));
        $demande2->setStatut(\App\Entity\DemandeEmprunt::STATUT_ACCEPTEE);
        $demande2->setDateTraitement(new \DateTime('-4 days'));
        // In a real scenario, we would link the Emprunt entity here if it existed in fixtures
        $manager->persist($demande2);

        // Sophie demande Dune (Refusée)
        $demande3 = new \App\Entity\DemandeEmprunt();
        $demande3->setAdherent($adherentEntities[1]); // Sophie
        $demande3->setDocument($docEntities[6]); // Dune
        $demande3->setBibliothecaire($biblio);
        $demande3->setDateDemande(new \DateTime('-1 week'));
        $demande3->setStatut(\App\Entity\DemandeEmprunt::STATUT_REFUSEE);
        $demande3->setDateTraitement(new \DateTime('-6 days'));
        $demande3->setMotifRefus("Livre abîmé, parti en réparation.");
        $manager->persist($demande3);

        $manager->flush();
    }
}
