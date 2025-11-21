
  
  // On sélectionne le formulaire
     var form = document.getElementById('monFormulaire');
     var editor = document.getElementById('editor');
     var inputCache = document.getElementById('contenuCache');



     // On écoute l'événement "submit" (quand on clique sur le bouton)
    form.addEventListener('submit', function(e) {
        
        
        // 1. On récupère tout le HTML (texte + gras + couleurs...) de la div
        const contenu = editor.innerHTML;
        
        // 2. On le met dans l'input caché
        inputCache.value = contenu;

        // 3. Le formulaire continue son envoi normalement vers le serveur...
        // (Si vous voulez vérifier avant d'envoyer, faites console.log(inputCache.value))
    });



    // 1. La fonction qui applique le style (Gras, Italique, etc.)
    function execCmd(command) {
        // Cette commande native fonctionne sur le texte sélectionné
        document.execCommand(command, false, null);
        // On remet le focus sur l'éditeur pour continuer à taper
        document.getElementById('editor').focus();
    }

    // 2. Gestion de l'envoi du formulaire
    const form = document.getElementById('myForm');
    const editor = document.getElementById('editor');
    const hiddenInput = document.getElementById('hiddenInput');

    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Empêche l'envoi réel pour la démo
        
        // On copie le HTML de la div éditable vers l'input caché
        hiddenInput.value = editor.innerHTML;
        
        console.log("Données prêtes à être envoyées :", hiddenInput.value);
        alert("Contenu enregistré (voir console) :\n" + editor.innerText);
        
        // Ici, vous pouvez enlever le e.preventDefault() si vous voulez envoyer vers PHP
        // form.submit(); 
    });
    
    // 3. Style CSS basique pour que les listes s'affichent correctement dans Tailwind
    // Tailwind supprime par défaut les puces des listes (ul/ol), on les remet ici juste pour l'éditeur.
    const style = document.createElement('style');
    style.innerHTML = `
        #editor ul { list-style-type: disc; margin-left: 1.5rem; }
        #editor ol { list-style-type: decimal; margin-left: 1.5rem; }
        #editor b, #editor strong { font-weight: bold; }
        #editor i, #editor em { font-style: italic; }
        #editor u { text-decoration: underline; }
    `;
    document.head.appendChild(style);


  

    
