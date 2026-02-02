<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Adherent;
use App\Entity\Document;
use App\Entity\Emprunt;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Admin
        $admin = new User();
        $admin->setEmail('admin@mediatheque.fr');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // Bibliothécaire 1
        $biblio1 = new User();
        $biblio1->setEmail('marie.dubois@mediatheque.fr');
        $biblio1->setRoles(['ROLE_BIBLIOTHECAIRE']);
        $biblio1->setPassword($this->passwordHasher->hashPassword($biblio1, 'biblio123'));
        $manager->persist($biblio1);

        // Bibliothécaire 2
        $biblio2 = new User();
        $biblio2->setEmail('pierre.martin@mediatheque.fr');
        $biblio2->setRoles(['ROLE_BIBLIOTHECAIRE']);
        $biblio2->setPassword($this->passwordHasher->hashPassword($biblio2, 'biblio123'));
        $manager->persist($biblio2);

        // ==========================================
        // 2. CRÉATION DES ADHÉRENTS
        // ==========================================
        
        $adherents = [];

        // Adhérent 1
        $adherent1 = new Adherent();
        $adherent1->setNom('Dupont');
        $adherent1->setPrenom('Jean');
        $adherent1->setEmail('jean.dupont@email.fr');
        $adherent1->setTelephone('0612345678');
        $adherent1->setAdresse('12 Rue de la Paix, 75001 Paris');
        $adherent1->setDateInscription(new \DateTime('2024-01-15'));
        $adherent1->setActif(true);
        $manager->persist($adherent1);
        $adherents[] = $adherent1;

        // Adhérent 2
        $adherent2 = new Adherent();
        $adherent2->setNom('Bernard');
        $adherent2->setPrenom('Sophie');
        $adherent2->setEmail('sophie.bernard@email.fr');
        $adherent2->setTelephone('0623456789');
        $adherent2->setAdresse('45 Avenue des Champs-Élysées, 75008 Paris');
        $adherent2->setDateInscription(new \DateTime('2024-02-20'));
        $adherent2->setActif(true);
        $manager->persist($adherent2);
        $adherents[] = $adherent2;

        // Adhérent 3
        $adherent3 = new Adherent();
        $adherent3->setNom('Petit');
        $adherent3->setPrenom('Lucas');
        $adherent3->setEmail('lucas.petit@email.fr');
        $adherent3->setTelephone('0634567890');
        $adherent3->setAdresse('78 Boulevard Saint-Germain, 75005 Paris');
        $adherent3->setDateInscription(new \DateTime('2024-03-10'));
        $adherent3->setActif(true);
        $manager->persist($adherent3);
        $adherents[] = $adherent3;

        // Adhérent 4
        $adherent4 = new Adherent();
        $adherent4->setNom('Robert');
        $adherent4->setPrenom('Emma');
        $adherent4->setEmail('emma.robert@email.fr');
        $adherent4->setTelephone('0645678901');
        $adherent4->setAdresse('23 Rue du Faubourg Saint-Honoré, 75008 Paris');
        $adherent4->setDateInscription(new \DateTime('2024-04-05'));
        $adherent4->setActif(true);
        $manager->persist($adherent4);
        $adherents[] = $adherent4;

        // Adhérent 5
        $adherent5 = new Adherent();
        $adherent5->setNom('Richard');
        $adherent5->setPrenom('Thomas');
        $adherent5->setEmail('thomas.richard@email.fr');
        $adherent5->setTelephone('0656789012');
        $adherent5->setAdresse('89 Rue de Rivoli, 75001 Paris');
        $adherent5->setDateInscription(new \DateTime('2023-11-20'));
        $adherent5->setActif(false); // Adhérent inactif
        $manager->persist($adherent5);
        $adherents[] = $adherent5;

        // ==========================================
        // 3. CRÉATION DES DOCUMENTS - LIVRES
        // ==========================================
        
        $documents = [];

        // Livre 1
        $livre1 = new Document();
        $livre1->setTitre('Le Petit Prince');
        $livre1->setAuteur('Antoine de Saint-Exupéry');
        $livre1->setType('Livre');
        $livre1->setIsbn('978-2-07-061275-8');
        $livre1->setDateAcquisition(new \DateTime('2023-01-15'));
        $livre1->setDisponible(true);
        $livre1->setResume('Un aviateur, suite à une panne de moteur, se retrouve perdu en plein désert du Sahara. Il y rencontre un petit prince venu d\'une autre planète.');
        $manager->persist($livre1);
        $documents[] = $livre1;

        // Livre 2
        $livre2 = new Document();
        $livre2->setTitre('1984');
        $livre2->setAuteur('George Orwell');
        $livre2->setType('Livre');
        $livre2->setIsbn('978-2-07-036822-8');
        $livre2->setDateAcquisition(new \DateTime('2023-02-10'));
        $livre2->setDisponible(false); // Emprunté
        $livre2->setResume('Dans un monde dystopique, Winston Smith travaille au Ministère de la Vérité où il réécrit l\'histoire selon les besoins du Parti.');
        $manager->persist($livre2);
        $documents[] = $livre2;

        // Livre 3
        $livre3 = new Document();
        $livre3->setTitre('L\'Étranger');
        $livre3->setAuteur('Albert Camus');
        $livre3->setType('Livre');
        $livre3->setIsbn('978-2-07-036002-4');
        $livre3->setDateAcquisition(new \DateTime('2023-03-05'));
        $livre3->setDisponible(true);
        $livre3->setResume('Meursault, un employé de bureau à Alger, apprend la mort de sa mère. Il assiste à l\'enterrement sans montrer d\'émotion.');
        $manager->persist($livre3);
        $documents[] = $livre3;

        // Livre 4
        $livre4 = new Document();
        $livre4->setTitre('Harry Potter à l\'école des sorciers');
        $livre4->setAuteur('J.K. Rowling');
        $livre4->setType('Livre');
        $livre4->setIsbn('978-2-07-054120-6');
        $livre4->setDateAcquisition(new \DateTime('2023-04-12'));
        $livre4->setDisponible(true);
        $livre4->setResume('Harry Potter découvre le jour de ses onze ans qu\'il est le fils orphelin de deux puissants sorciers.');
        $manager->persist($livre4);
        $documents[] = $livre4;

        // Livre 5
        $livre5 = new Document();
        $livre5->setTitre('Le Seigneur des Anneaux');
        $livre5->setAuteur('J.R.R. Tolkien');
        $livre5->setType('Livre');
        $livre5->setIsbn('978-2-266-15410-5');
        $livre5->setDateAcquisition(new \DateTime('2023-05-20'));
        $livre5->setDisponible(false); // Emprunté
        $livre5->setResume('Frodo Sacquet hérite d\'un anneau magique qui pourrait permettre au seigneur des ténèbres de dominer la Terre du Milieu.');
        $manager->persist($livre5);
        $documents[] = $livre5;

        // ==========================================
        // 4. CRÉATION DES DOCUMENTS - CD
        // ==========================================

        // CD 1
        $cd1 = new Document();
        $cd1->setTitre('Thriller');
        $cd1->setAuteur('Michael Jackson');
        $cd1->setType('CD');
        $cd1->setIsbn(null);
        $cd1->setDateAcquisition(new \DateTime('2023-06-01'));
        $cd1->setDisponible(true);
        $cd1->setResume('Album le plus vendu de tous les temps, sorti en 1982. Contient des hits comme Billie Jean et Beat It.');
        $manager->persist($cd1);
        $documents[] = $cd1;

        // CD 2
        $cd2 = new Document();
        $cd2->setTitre('Abbey Road');
        $cd2->setAuteur('The Beatles');
        $cd2->setType('CD');
        $cd2->setIsbn(null);
        $cd2->setDateAcquisition(new \DateTime('2023-06-15'));
        $cd2->setDisponible(true);
        $cd2->setResume('Onzième album studio des Beatles, sorti en 1969. Inclut Come Together et Here Comes the Sun.');
        $manager->persist($cd2);
        $documents[] = $cd2;

        // CD 3
        $cd3 = new Document();
        $cd3->setTitre('Random Access Memories');
        $cd3->setAuteur('Daft Punk');
        $cd3->setType('CD');
        $cd3->setIsbn(null);
        $cd3->setDateAcquisition(new \DateTime('2023-07-10'));
        $cd3->setDisponible(false); // Emprunté
        $cd3->setResume('Quatrième album studio du duo français, sorti en 2013. Contient le hit mondial Get Lucky.');
        $manager->persist($cd3);
        $documents[] = $cd3;

        // ==========================================
        // 5. CRÉATION DES DOCUMENTS - DVD
        // ==========================================

        // DVD 1
        $dvd1 = new Document();
        $dvd1->setTitre('Inception');
        $dvd1->setAuteur('Christopher Nolan');
        $dvd1->setType('DVD');
        $dvd1->setIsbn(null);
        $dvd1->setDateAcquisition(new \DateTime('2023-08-05'));
        $dvd1->setDisponible(true);
        $dvd1->setResume('Dom Cobb est un voleur expérimenté qui s\'infiltre dans les rêves pour voler des secrets.');
        $manager->persist($dvd1);
        $documents[] = $dvd1;

        // DVD 2
        $dvd2 = new Document();
        $dvd2->setTitre('Le Parrain');
        $dvd2->setAuteur('Francis Ford Coppola');
        $dvd2->setType('DVD');
        $dvd2->setIsbn(null);
        $dvd2->setDateAcquisition(new \DateTime('2023-08-20'));
        $dvd2->setDisponible(true);
        $dvd2->setResume('L\'histoire de la famille Corleone, une dynastie mafieuse new-yorkaise.');
        $manager->persist($dvd2);
        $documents[] = $dvd2;

        // DVD 3
        $dvd3 = new Document();
        $dvd3->setTitre('La Liste de Schindler');
        $dvd3->setAuteur('Steven Spielberg');
        $dvd3->setType('DVD');
        $dvd3->setIsbn(null);
        $dvd3->setDateAcquisition(new \DateTime('2023-09-10'));
        $dvd3->setDisponible(false); // Emprunté
        $dvd3->setResume('L\'histoire vraie d\'Oskar Schindler qui sauva plus de mille Juifs durant l\'Holocauste.');
        $manager->persist($dvd3);
        $documents[] = $dvd3;

        // ==========================================
        // 6. CRÉATION DES EMPRUNTS
        // ==========================================

        // Emprunt 1 - En cours
        $emprunt1 = new Emprunt();
        $emprunt1->setAdherent($adherent1);
        $emprunt1->setDocument($livre2); // 1984
        $emprunt1->setDateEmprunt(new \DateTime('2025-01-20'));
        $emprunt1->setDateRetourPrevue(new \DateTime('2025-02-20'));
        $emprunt1->setDateRetourEffective(null);
        $emprunt1->setStatut('en_cours');
        $manager->persist($emprunt1);

        // Emprunt 2 - En cours
        $emprunt2 = new Emprunt();
        $emprunt2->setAdherent($adherent2);
        $emprunt2->setDocument($livre5); // Le Seigneur des Anneaux
        $emprunt2->setDateEmprunt(new \DateTime('2025-01-25'));
        $emprunt2->setDateRetourPrevue(new \DateTime('2025-02-25'));
        $emprunt2->setDateRetourEffective(null);
        $emprunt2->setStatut('en_cours');
        $manager->persist($emprunt2);

        // Emprunt 3 - En retard
        $emprunt3 = new Emprunt();
        $emprunt3->setAdherent($adherent3);
        $emprunt3->setDocument($cd3); // Daft Punk
        $emprunt3->setDateEmprunt(new \DateTime('2025-01-05'));
        $emprunt3->setDateRetourPrevue(new \DateTime('2025-01-20'));
        $emprunt3->setDateRetourEffective(null);
        $emprunt3->setStatut('en_retard');
        $manager->persist($emprunt3);

        // Emprunt 4 - En retard
        $emprunt4 = new Emprunt();
        $emprunt4->setAdherent($adherent4);
        $emprunt4->setDocument($dvd3); // La Liste de Schindler
        $emprunt4->setDateEmprunt(new \DateTime('2025-01-10'));
        $emprunt4->setDateRetourPrevue(new \DateTime('2025-01-25'));
        $emprunt4->setDateRetourEffective(null);
        $emprunt4->setStatut('en_retard');
        $manager->persist($emprunt4);

        // Emprunt 5 - Terminé
        $emprunt5 = new Emprunt();
        $emprunt5->setAdherent($adherent1);
        $emprunt5->setDocument($livre1); // Le Petit Prince
        $emprunt5->setDateEmprunt(new \DateTime('2024-12-01'));
        $emprunt5->setDateRetourPrevue(new \DateTime('2025-01-01'));
        $emprunt5->setDateRetourEffective(new \DateTime('2024-12-28'));
        $emprunt5->setStatut('termine');
        $manager->persist($emprunt5);

        // Emprunt 6 - Terminé
        $emprunt6 = new Emprunt();
        $emprunt6->setAdherent($adherent2);
        $emprunt6->setDocument($livre3); // L'Étranger
        $emprunt6->setDateEmprunt(new \DateTime('2024-11-15'));
        $emprunt6->setDateRetourPrevue(new \DateTime('2024-12-15'));
        $emprunt6->setDateRetourEffective(new \DateTime('2024-12-10'));
        $emprunt6->setStatut('termine');
        $manager->persist($emprunt6);

        // Emprunt 7 - Terminé
        $emprunt7 = new Emprunt();
        $emprunt7->setAdherent($adherent3);
        $emprunt7->setDocument($livre4); // Harry Potter
        $emprunt7->setDateEmprunt(new \DateTime('2024-10-20'));
        $emprunt7->setDateRetourPrevue(new \DateTime('2024-11-20'));
        $emprunt7->setDateRetourEffective(new \DateTime('2024-11-18'));
        $emprunt7->setStatut('termine');
        $manager->persist($emprunt7);

        // Sauvegarder toutes les données
        $manager->flush();
    }
}
