# Carpathia-Travel
Proiect la materia Dezvoltarea Aplicatiilor Web realizat de Simion Louis, student la Universitatea din Bucuresti, Informatica ID, an 2, cu tematica "Activitatile unei agentii de turism" 
Realizat folosind urmatoarele limbaje de programare: PHP, HTML, CSS, SQL, JavaScript

<---------->

  <?> Descriere succinta a bazei de date: <?>

Tabele: comenzi, comenzi_produse, cos_cumparaturi, imagini_produse, produse, utilizatori

Fiecare comanda din tabela "comenzi" contine id_utilizator ca FK al tabelei "utilizatori", iar tabela "comenzi_produse" contine id_comanda ca FK al tabelei "comenzi".
La tabela "imagini_produse", fiecare imagine are o ordine de afisare si este specifica unui id_produs FK de la tabela "produse".
Fiecare pachet turistic (produs) are un tip_pachet specific (City Break, Circuit, Litoral, Munte).
