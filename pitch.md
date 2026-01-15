# Pitch – Plateforme de Test Technique

## Objectif
Créer une plateforme accessible via un lien envoyé par mail permettant à des candidats de passer un test technique sous forme de quiz. Le quiz est généré aléatoirement à partir d'une base de questions. Les résultats sont récapitulés dans une table dédiée.

## Synthèse du Brainstorming

**1. Public cible** : Candidats (utilisateurs invités à passer le test)

**2. Type de questions** : QCM uniquement

**3. Nombre de tests** : Plusieurs tests techniques différents peuvent être gérés

**4. Gestion de la base de questions** :
- Import initial de questions
- Ajout manuel de nouvelles questions via l’interface d’administration

**5. Identification** :
- Les candidats renseignent leur nom et prénom (pas de session utilisateur)

**6. Correction** :
- Correction automatique des réponses

**7. Statistiques** :
- Statistiques avancées (temps de réponse, taux de réussite, etc.)

**8. Administration** :
- Interface d’administration pour gérer les questions, les tests et les résultats
- Seuls les administrateurs ont accès au panneau de contrôle

**9. Contraintes techniques** :
- Utilisation de Symfony 7 ou 8

**10. Lien d’accès** :
- Le lien envoyé par mail expire après utilisation

**11. Personnalisation des tests** :
- Personnalisation possible (durée, nombre de questions, etc.)

**12. Gestion des utilisateurs** :
- Pas de gestion d’utilisateurs côté candidats
- Gestion des administrateurs uniquement

**13. Notifications** :
- Mail envoyé aux administrateurs quand un test est rempli

**14. Sécurité et conformité** :
- Exigences de base pour être conforme (ex : RGPD)

**15. Design/UX** :
- Pas d’exigence particulière pour le design ou l’expérience utilisateur

---

Ce document synthétise les besoins et fonctionnalités pour démarrer le projet.
