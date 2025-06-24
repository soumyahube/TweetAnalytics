const PUBLIC_KEY = "UYVY9zDxMKA0GJgAL"; // Définir une seule fois

emailjs.init(PUBLIC_KEY); // Initialisation d'EmailJS avec la clé publique

fetch('feedbacks.json')
  .then(response => response.json())
  .then(data => {
    data.forEach(vision => {
      if (parseInt(vision.rating, 10) > 3) {
        emailjs.send('service_a3a91we', 'template_rjqnytf', {
          name: vision.name,
          email: vision.email,
        }, PUBLIC_KEY) // Utilisation de la variable
        .then(response => console.log(`✅ Email envoyé à ${vision.name} !`))
        .catch(error => console.error('❌ Erreur lors de l’envoi', error));
      }
    });
  })
  .catch(error => console.error("❌ Erreur de lecture du fichier JSON", error));
