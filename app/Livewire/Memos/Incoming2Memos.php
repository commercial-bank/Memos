<?php

namespace App\Livewire\Memos;

use Carbon\Carbon;
use App\Models\Memo;
use App\Models\User;
use App\Models\Entity;
use Livewire\Component;
use App\Models\References;
use App\Models\Historiques;
use App\Models\MemoHistory;
use Illuminate\Support\Str;
use App\Models\ReplacesUser;
use Illuminate\Support\Facades\Auth;
use App\Models\BlocEnregistrements; 
use Illuminate\Support\Facades\DB; 

class Incoming2Memos extends Component
{

    // --- RECHERCHE & DATATABLE ---
    public $search = '';

    // --- VARIABLES MODAL ASSIGNATION ---
    public $memo_type = 'standard'; // 'standard' ou 'projet'

    // Structure des données pour l'affichage : ['original' => User, 'effective' => User, 'is_replaced' => bool]
    public $managerData = null; 

    // --- AJOUTER CES PROPRIÉTÉS ---
    public $isTransModalOpen = false; // État du modal
    public $targetRecipients = [];    // La liste des utilisateurs trouvés (Objets User)
    public $selectedRecipients = [];  // Les IDs cochés par l'utilisateur
    public $targetRoleName = '';      // Le nom du rôle (ex: "Sous-Directeurs")
    public $memoIdToTrans = null;     // L'ID du mémo en cours de traitement

    // Liste des utilisateurs éligibles pour le mode projet (Excluant Auth et N+1)
    public $projectUsersList = []; 

    // IDs sélectionnés en mode projet
    public $selected_project_users = [];

    // --- VARIABLES MODAL TRANSMISSION (SECRÉTAIRE) ---
    public $isOpenTrans = false;
    public $transRecipients = []; // Liste des secrétaires destinataires
    public $generatedReference = ''; // Pour stocker la ref calculée avant enregistrement (optionnel)

     // --- MODALS STATES ---
    public $isOpen = false;

     // --- DATA VIEW (Aperçu) ---
    public $date;
    public $user_service;
    public $user_first_name;
    public $user_last_name;
    public $user_entity_name;
    public $selections = []; // Pour modal assignation simple

     // AJOUTER CETTE VARIABLE
    public $comment = ''; // Pour stocker le commentaire saisi dans le modal


    #[Rule('required|string|max:255')]
    public string $object = '';

    #[Rule('required|string|max:255')]
    public string $concern = '';

    #[Rule('required|string')]
    public string $content = '';

 
 




    // --- VARIABLES ENREGISTREMENT (MODIFIÉES) ---
    public $isRegistrationModalOpen = false;
    
    #[Rule('required|string')]
    public $reg_reference = ''; 

    #[Rule('required|string')]
    public $reg_nature = '';    

    #[Rule('required|string')]
    public $reg_objet = '';     // NOUVEAU

    #[Rule('required|string')]
    public $reg_expediteur = ''; // NOUVEAU

    #[Rule('required|string')]
    public $reg_date = '';


    // --- CHAMPS DU MÉMO (SCHEMA DB) ---
    public $memo_id = null;

    public function viewMemo($id) {
        $memo = Memo::with('user')->findOrFail($id);
        $this->fillMemoDataView($memo);
        $this->isOpen = true;
    }

    private function fillMemoDataView($memo) {
        // Logique simplifiée pour l'aperçu lecture seule
        $this->memo_id = $memo->id;
        $this->object = $memo->object;
        $this->concern = $memo->concern;
        $this->content = $memo->content;
        $this->date = $memo->created_at->format('d/m/Y');
        $entity = Entity::find($memo->user->entity_id);
        $this->user_entity_name = $entity->name ?? 'Entité';
        $this->user_service = $memo->user->service;
    }

    public function closeModal() { $this->isOpen = false; }

    /**
     * POINT D'ENTRÉE : Clic sur le bouton "Avion"
     */
     public function transMemo($id)
    {
        $this->resetValidation();
        $this->memoIdToTrans = $id;
        $this->comment = '';
        $this->selectedRecipients = [];
        
        // On charge le mémo avec l'utilisateur et son entité pour récupérer le nom
        $memo = Memo::with('user.entity')->find($id);

        if (!$memo) return;

        $user = Auth::user();
        $poste = Str::lower(trim($user->poste));

        // CAS SPÉCIFIQUE : SECRÉTAIRE
        if (Str::contains($poste, 'secretaire')) {
            
            // 1. Pré-remplissage
            $this->reg_reference = $memo->reference ?? '';
            $this->reg_date = Carbon::now()->format('d/m/Y');
            
            // 2. Pré-remplissage séparé
            $this->reg_objet = $memo->object; // L'objet du mémo
            $this->reg_nature = 'Memo Entrant'; // Valeur par défaut (modifiable)
            
            // 3. Récupération automatique de l'entité de l'expéditeur (User du mémo)
            $this->reg_expediteur = $memo->user->entity->name ?? 'Entité Inconnue';

            $this->isRegistrationModalOpen = true; 
        } 
        else {
            $this->prepareTransmission($id);
        }
    }

      public function saveRegistrationAndContinue()
        {
            $this->validate([
                'reg_reference' => 'required|string',
                'reg_nature' => 'required|string',
                'reg_objet' => 'required|string',
                'reg_expediteur' => 'required|string',
                'reg_date' => 'required|string',
            ]);

            $user = Auth::user();

            // Enregistrement avec les champs distincts
            BlocEnregistrements::create([
                'reference'          => $this->reg_reference,
                'date_enreg'         => $this->reg_date,
                'nature_memo'        => $this->reg_nature,        // Champ distinct
                'objet'              => $this->reg_objet,         // Champ distinct
                'memo_id'            => $this->memoIdToTrans,
                'user_id'            => $user->id,
            ]);

            // Mise à jour de la réf dans le mémo original si nécessaire
            $memo = Memo::find($this->memoIdToTrans);
            if ($memo && empty($memo->reference)) {
                $memo->update(['reference' => $this->reg_reference]);
            }

            $this->isRegistrationModalOpen = false;
            $this->dispatch('notify', message: "Enregistrement effectué. Passage à la transmission.");

            // On enchaîne sur la transmission
            $this->prepareTransmission($this->memoIdToTrans);
        }

     /**
     * ÉTAPE 2 : Préparation de la liste des destinataires (Logique existante extraite)
     */
    private function prepareTransmission($id)
    {
        $memo = Memo::find($id);
        $user = Auth::user();
        $poste = Str::lower(trim($user->poste)); 
        $query = User::query();

        // --- VOTRE LOGIQUE HIÉRARCHIQUE EXISTANTE ---
        if (Str::contains($poste, 'secretaire')) {
            $this->targetRoleName = 'Directeur';
            $query->where('poste', 'like', '%Directeur%')
                ->where('entity_id', $user->entity_id)
                ->where('id', '!=', $user->id);
        }
        elseif (Str::contains($poste, 'directeur') && !Str::contains($poste, 'sous')) {
            $this->targetRoleName = 'Sous-Directeur';
            $query->where('poste', 'like', '%Sous-Directeur%')
                ->where('entity_id', $user->entity_id);
        }
        elseif (Str::contains($poste, 'sous-directeur')) {
            $this->targetRoleName = 'Chef de Département';
            $query->where('poste', 'like', '%Chef-Departement%');
            if ($user->sous_direction_id) {
                $query->where('sous_direction_id', $user->sous_direction_id);
            } else {
                $query->where('entity_id', $user->entity_id);
            }
        }
        elseif (Str::contains($poste, 'chef-departement')) {
            $this->targetRoleName = 'Chef de Service';
            $query->where('poste', 'like', '%Chef-Service%')
                ->where('departement', $user->departement)
                ->where('entity_id', $user->entity_id);
        }
        elseif (Str::contains($poste, 'chef-service')) {
            $this->targetRoleName = 'Collaborateurs';
            $query->where('service', $user->service)
                ->where('entity_id', $user->entity_id)
                ->where('id', '!=', $user->id)
                ->where(function($q) {
                    $q->where('poste', 'not like', '%Directeur%')
                        ->where('poste', 'not like', '%Chef-Departement%');
                });
        }
        else {
            $this->dispatch('notify', message: "Votre poste ne permet pas la transmission automatique.");
            return;
        }

        $this->targetRecipients = $query->get();

        if ($this->targetRecipients->isEmpty()) {
            $this->dispatch('notify', message: "Aucun {$this->targetRoleName} trouvé dans votre structure.");
            return;
        }

        if ($this->targetRecipients->count() === 1) {
            $this->selectedRecipients[] = $this->targetRecipients->first()->id;
        }

        // OUVERTURE DU MODAL DE TRANSMISSION
        $this->isTransModalOpen = true;
    }

    public function closeRegistrationModal()
    {
        $this->isRegistrationModalOpen = false;
        $this->memoIdToTrans = null;
    }

   
    public function closeTransModal()
    {
        $this->isTransModalOpen = false;
        $this->selectedRecipients = [];
        $this->comment = '';
        $this->memoIdToTrans = null;
        $this->targetRecipients = [];
    }

    public function confirmTransmission()
    {
        // 1. Validation
        $this->validate([
            'selectedRecipients' => 'required|array|min:1',
        ], [
            'selectedRecipients.required' => 'Veuillez sélectionner au moins un destinataire.',
        ]);

        $memo = Memo::find($this->memoIdToTrans);
        if (!$memo) {
            $this->dispatch('notify', message: "Erreur : Mémo introuvable.");
            $this->closeTransModal();
            return;
        }

        $senderId = Auth::id();

        // 2. Gestion de la colonne 'current_holders' (JSON)
        // On récupère les détenteurs actuels
        $holders = $memo->current_holders;
        
        // Si c'est null ou pas un tableau, on initialise
        if (is_null($holders)) {
            $holders = [];
        } elseif (is_string($holders)) {
            // Au cas où le cast 'array' n'est pas fait dans le modèle
            $holders = json_decode($holders, true) ?? [];
        }

        // A. Retirer l'utilisateur courant (l'expéditeur) de la liste des détenteurs
        // (Note: array_values pour réindexer proprement le tableau JSON)
        $holders = array_values(array_diff($holders, [$senderId]));

        // B. Ajouter les nouveaux destinataires s'ils n'y sont pas déjà
        foreach ($this->selectedRecipients as $recipientId) {
            // On s'assure que c'est un entier
            $rId = (int) $recipientId;
            if (!in_array($rId, $holders)) {
                $holders[] = $rId;
            }
        }

        // C. Sauvegarde des nouveaux détenteurs
        $memo->current_holders = $holders;
        
        // Optionnel : Mettre à jour le statut si nécessaire
        // $memo->status = 'transmis'; 
        
        $memo->save();

        // 3. Enregistrement de l'historique (Traçabilité)
        foreach ($this->selectedRecipients as $recipientId) {
            

             Historiques::create([
            'user_id' => $senderId,
            'memo_id' => $memo->id,
            'visa'    => 'cote',
            'workflow_comment' => $this->comment
        ]);
            
            // Si vous utilisez aussi la table 'destinataires' (relation hasMany), 
            // il faudrait peut-être ajouter une entrée ici, dépendamment de votre architecture.
        }

        // 4. Feedback et Fermeture
        $this->dispatch('notify', message: "Mémo transmis avec succès.");
        $this->closeTransModal();
    }

    public function render()
    {
       // On récupère l'ID tel quel (c'est un entier par défaut dans Laravel)
        $userId = Auth::id(); 

        $memos = Memo::with(['user', 'destinataires.entity'])
            ->where('workflow_direction', 'entrant')
            
            // Laravel va chercher l'entier dans le tableau JSON
            ->whereJsonContains('current_holders', $userId)
            
            // Recherche
            ->where(function($query) {
                $query->where('object', 'like', '%'.$this->search.'%')
                    ->orWhere('concern', 'like', '%'.$this->search.'%');
            })
            
            // Tri
            ->orderBy('updated_at', 'desc')
            ->paginate(9);

        return view('livewire.memos.incoming2-memos', [
            'memos' => $memos,
        ]);
    }
}
