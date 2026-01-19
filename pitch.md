Table,Rôle Principal,Attributs Clés
Questions,Stockage des énoncés,"Texte, Type (Unique/Multi), Niveau, Feedback, Image_Blob, Code_Markdown."
Answers,Réponses possibles,"Texte, is_correct (booléen)."
Quiz_Templates,"La ""recette"" du quiz","Nom, Type (Entraînement/Examen), Mode (Aléo/Équil/Fixe), Timer_Total."
Quiz_Rules,Règles pour le mode équilibré,"Template_id, Thème_id, Niveau, Quantité de questions."
Users,Étudiants enregistrés,"Nom, Email, Password, Role."
Invitations,Accès candidat externe,"Token unique, Date expiration, Max_attempts."
Quiz_Sessions,Une instance de quiz lancée,"User_id (null si invité), Invitation_id, Score_final, Temps_total."
User_Responses,Détail des réponses,"Session_id, Question_id, Answer_id, Time_spent_on_q."