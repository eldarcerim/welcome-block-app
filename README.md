# Welcome Block App – Community Services

A free, open-source bilingual WordPress welcome section for community service centres and organisations, with two diagonal images, centred copy and an orange call-to-action button.

Besplatan dvojezični WordPress uvodni blok otvorenog koda za centre i organizacije koje pružaju usluge u zajednici, s dvije dijagonalne fotografije, centralnim tekstom i narandžastim dugmetom.

**[Preuzmi instalacijski ZIP / Download the installable ZIP](release/welcome-block-app-1.1.0.zip)**

## Bosanski

Projekat je objavljen pod licencom GPL-2.0-or-later. Centri, udruženja, ustanove i druge organizacije mogu ga besplatno koristiti, prilagođavati i dijeliti u skladu s licencom. Fotografije, bosanski i engleski tekst te dugme uređuju se u WordPress administraciji, bez izmjene koda.

### Instalacija

1. Preuzmite instalacijski ZIP putem linka na vrhu ove stranice ili iz foldera `release`.
2. U WordPressu otvorite **Dodaci → Dodaj novi → Prenesi dodatak**.
3. Instalirajte i aktivirajte dodatak.
4. Otvorite **MOST dobrodošlica**.
5. Odaberite lijevu i desnu fotografiju te uredite bosanski i engleski tekst.
6. Na bosansku stranicu dodajte:

```text
[welcome_block lang="bs"]
```

Na englesku stranicu dodajte:

```text
[welcome_block lang="en"]
```

Bez `lang` atributa koristi se jezik trenutne WordPress stranice. Raniji `[most_welcome]` shortcode ostaje podržan radi kompatibilnosti.

Na računaru fotografije ulaze dijagonalno s lijeve i desne strane. Na mobitelu je raspored: **lijeva fotografija → tekst i dugme → desna fotografija**.

## English

The project is published under GPL-2.0-or-later. Community centres, associations, institutions and other organisations may use, adapt and redistribute it free of charge in accordance with the licence. Images, Bosnian and English copy, and the button can be edited in WordPress without changing the code.

Download the installable ZIP using the link at the top of this page, install it through **Plugins → Add New → Upload Plugin**, activate it and open **Welcome Block**. Select both images and edit the Bosnian and English copy.

```text
[welcome_block lang="en"]
```

Use `[welcome_block lang="bs"]` for Bosnian. Without the attribute, the plugin follows the current WordPress locale. The earlier `[most_welcome]` shortcode remains supported for compatibility.

On desktop, the photos enter diagonally from both sides. On mobile the order is **left photo → text and button → right photo**.

## Requirements

- WordPress 6.2 or newer
- PHP 7.4 or newer

## License

GPL-2.0-or-later.
