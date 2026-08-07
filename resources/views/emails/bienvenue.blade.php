@component('mail::message')
# Bonjour, bienvenue sur HRIS !

Voici les coordonnées de votre compte :

@component('mail::panel')
**Vos identifiants de connexion :**

- **Email** : {{ $utilisateur->email }}
- **Mot de passe** : **{{ $password }}**
@endcomponent

@component('mail::button', ['url' => url('/login')])
Se connecter maintenant
@endcomponent

**Conseil de sécurité** : Changez votre mot de passe dès votre première connexion.

Cordialement,  
L'équipe administrative
@endcomponent