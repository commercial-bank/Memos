<?php

namespace App\Livewire\Memos;

use Livewire\Component;

class IncomingMemos extends Component
{
    public $memos;
  
    
    public function mount()
    {
        $this->memos = [
            (object)['id' => 1, 'from' => 'Amel Ngatchou', 'author' => 'DTDSI', 'date' => '18/02/2002', 'subject' => 'Memo sur le projet Alpha', 'content' => 'Bonjour l\'équipe, je voulais juste vous donner une mise à jour rapide sur l\'avancement du projet Alpha. Nous avons terminé la phase de conception et nous sommes maintenant en train de développer les modules principaux. La date limite est toujours le 15 du mois prochain.'],
            (object)['id' => 2, 'from' => 'John Doe', 'author' => 'IT Dept', 'date' => '19/02/2002', 'subject' => 'Mise à jour importante du système', 'content' => 'Une mise à jour critique du système aura lieu ce week-end, le samedi 22 février, de 22h00 à 2h00 du matin. Pendant cette période, certains services pourraient être temporairement indisponibles. Veuillez planifier en conséquence.'],
            (object)['id' => 3, 'from' => 'Jane Smith', 'author' => 'HR', 'date' => '20/02/2002', 'subject' => 'Rappel de la réunion mensuelle', 'content' => 'Juste un rappel amical que notre réunion mensuelle aura lieu demain, le 21 février, à 10h00 dans la salle de conférence principale. L\'ordre du jour a été envoyé par e-mail plus tôt dans la semaine. N\'hésitez pas à nous faire part de vos questions.'],
            (object)['id' => 4, 'from' => 'Service Commercial', 'author' => 'Ventes', 'date' => '21/02/2002', 'subject' => 'Nouveaux objectifs trimestriels', 'content' => 'Nous sommes ravis d\'annoncer les nouveaux objectifs de vente pour le prochain trimestre. Nous avons des ambitions élevées et nous sommes convaincus qu\'avec les efforts de chacun, nous les atteindrons. Plus de détails seront partagés lors de la réunion de lundi.'],
            (object)['id' => 5, 'from' => 'Direction Générale', 'author' => 'DG', 'date' => '22/02/2002', 'subject' => 'Communication interne - Événement d\'entreprise', 'content' => 'Nous sommes heureux de vous inviter à notre événement annuel d\'entreprise, qui aura lieu le 15 mars. Ce sera une excellente occasion de se retrouver et de célébrer nos succès. Un e-mail avec les détails de l\'inscription suivra sous peu.'],
            (object)['id' => 6, 'from' => 'Direction Générale', 'author' => 'DG', 'date' => '22/02/2002', 'subject' => 'Communication interne - Événement d\'entreprise', 'content' => 'Nous sommes heureux de vous inviter à notre événement annuel d\'entreprise, qui aura lieu le 15 mars. Ce sera une excellente occasion de se retrouver et de célébrer nos succès. Un e-mail avec les détails de l\'inscription suivra sous peu.'],
            (object)['id' => 5, 'from' => 'Direction Générale', 'author' => 'DG', 'date' => '22/02/2002', 'subject' => 'Communication interne - Événement d\'entreprise', 'content' => 'Nous sommes heureux de vous inviter à notre événement annuel d\'entreprise, qui aura lieu le 15 mars. Ce sera une excellente occasion de se retrouver et de célébrer nos succès. Un e-mail avec les détails de l\'inscription suivra sous peu.'],
            (object)['id' => 6, 'from' => 'Direction Générale', 'author' => 'DG', 'date' => '22/02/2002', 'subject' => 'Communication interne - Événement d\'entreprise', 'content' => 'Nous sommes heureux de vous inviter à notre événement annuel d\'entreprise, qui aura lieu le 15 mars. Ce sera une excellente occasion de se retrouver et de célébrer nos succès. Un e-mail avec les détails de l\'inscription suivra sous peu.'],
        ];
    }


   

    public function render()
    {
        return view('livewire.memos.incoming-memos');
    }
}