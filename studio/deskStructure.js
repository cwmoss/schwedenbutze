import { MdSettings } from 'react-icons/md'
import { MdPerson } from 'react-icons/md'
import { SlPencil } from 'react-icons/sl'

import { HtmlPreview } from './htmlpreview'

const hiddenDocTypes = listItem =>
  !['category', 'author', 'post', 'page', 'siteSettings', 'reservation', 'documentation'].includes(
    listItem.getId()
  )

export default S =>
  S.list()
    .title('Inhalt')
    .items([
      S.listItem()
        .title('Seiten')
        .schemaType('page')
        .child(S.documentTypeList('page').title('Seiten')),
      S.listItem()
        .title('Artikel')
        .schemaType('post')
        .child(S.documentTypeList('post').title('Artikel')),
      S.listItem()
        .title('Reservations')
        .child(
          S.list()
            .title('Reservations')
            .items([
              S.listItem()
                .title('Up next')
                .child(
                  S.documentList()
                    .title('Upcoming Reservations')
                    .filter(`_type == "reservation" && arrival >= $from || departure >= $from`)
                    .params({ from: new Date().toISOString() })
                ),
              S.listItem()
                .title('Past')
                .child(
                  S.documentList()
                    .title('Past Reservations')
                    .filter(`_type == "reservation" && departure < $today`)
                    .params({ today: new Date().toISOString() })
                ),
              S.documentTypeListItem('reservation').title('All Reservations')
            ])
        ),

      S.listItem()
        .title('Autoren')
        .icon(MdPerson)
        .schemaType('author')
        .child(S.documentTypeList('author').title('Autoren')),

      S.listItem()
        .title('Kategorien')
        .schemaType('category')
        .child(S.documentTypeList('category').title('Kategorien')),

      S.listItem()
        .title('Entwürfe')
        .icon(SlPencil)
        .child(
          S.documentList()
            .title('Entwürfe')
            .filter("_id in path('drafts.**')")
            .defaultOrdering([{ field: '_updatedAt', direction: 'desc' }])
        ),

      S.listItem()
        .title('Einstellungen')
        .icon(MdSettings)
        .child(
          S.editor()
            .id('siteSettings')
            .schemaType('siteSettings')
            .documentId('siteSettings')
        ),

      S.listItem()
        .title('Dokumentation')
        .child(
          S.documentTypeList('documentation')
            .defaultOrdering([{ field: 'title', direction: 'asc' }])
            .title('Dokumentation')
            .child(documentId =>
              S.document()
                .documentId(documentId)
                .schemaType('documentation')
                .views([S.view.component(HtmlPreview).title('Ansicht'), S.view.form()])
            )
        ),

      // This returns an array of all the document types
      // defined in schema.js. We filter out those that we have
      // defined the structure above
      ...S.documentTypeListItems().filter(hiddenDocTypes)
    ])
