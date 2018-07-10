# paginateur
classe pour pagination en PHP à partir de deux requêtes sql
une pour count(*)
et une avec select from where pour selection de tous les enregistrements à paginer
En retour nous obtenons un tableau à 2 dimensions représentant la liste à afficher et aussi  des variables avec href pour page suivante et précédente ainsi qu'un tableau avec des href pour afficher une liste des pages en accès rapide
Nous recupérons le numéro de la page à afficher ainsi que le nombre de lignes à afficher par page dans l'URL (page et lignes) 
