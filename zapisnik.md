# PORTAL

## TODO

Na pregledu objave sloziti po published_at DESC
Objavu nije moguce seci na neku duzinu zbog tagova
Nema teorije da bilo kako integrisem editor da moze da uploaduje fajlove.
Generalno mora da se napravi mini file manager (sa uploadom) na stranici koji ce da vraca link na fajl (sliku), a onda rucno da se taj link ubaci u editor.
Ovaj uploader za timyMCE skoro da radi, ali nemam pojma kako da prosledim csrf u php, a jos manje kako da vratim isti na stranicu.



Za sada bih terao bez komentara.
File manager i wysiwyg editor.

Vrste (enum u kategorijama):
- Opste
- Uprave
- Sindikati

Kategorije:
- Vesti (opste)
- Saopstenja (opste)
- Covid-19 (opste)
- Adresar (link na IKT imenik)
- JP i ustanove (opste)
- Uprave... (uprave)
- Samostalni (sindikati)
- Nezavisnost (sindikati)
- Pera? (sindikati) ovo kao da je Stanislav umesao prste (Jes)

Korisnici (svaki korisnik moze da promeni svoju lozinku):
- 0		admin
	- moze da dodaje i menja kategorije (mozda deleted_at)
	- vidi objave svih autora
	- moze da vrati obrisane objave
	- vidi logove
- 100	autor
	- moze da dodaje objave u kategorijama koje su mu dodeljene
	- moze da menja i brise samo svoje objave (brisanje deleted_at)

STASHA
deleted_at mora default na null da se postavi u bazi
