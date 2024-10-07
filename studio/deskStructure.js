import { FaCat } from 'react-icons/fa'
import { SlPencil, SlBookOpen, SlCalender, SlSettings, SlDoc, SlOrganization, SlFolderAlt } from 'react-icons/sl'
import { FaBeer } from 'react-icons/fa'

import { HtmlPreview } from './htmlpreview'

const hiddenDocTypes = listItem =>
  !['category', 'author', 'post', 'page', 'siteSettings', 'reservation', 'documentation'].includes(
    listItem.getId()
  )

export default S =>
  S.list()
    .title('Content')
    .items([
      S.listItem()
        .title('Pages')
        .icon(SlFolderAlt)
        .schemaType('page')
        .child(S.documentTypeList('page').title('Pages')),
      S.listItem()
        .title('Articles')
        .icon(SlDoc)
        .schemaType('post')
        .child(S.documentTypeList('post').title('Articles')),
      S.listItem()
        .title('Reservations')
        .icon(SlCalender)
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
        .title('Authors')
        .icon(FaCat)
        .schemaType('author')
        .child(S.documentTypeList('author').title('Authors')),

      S.listItem()
        .title('Categories')
        .icon(SlOrganization)
        .schemaType('category')
        .child(S.documentTypeList('category').title('Categries')),

      S.listItem()
        .title('Drafts')
        .icon(SlPencil)
        .child(
          S.documentList()
            .title('Drafts')
            .filter("_id in path('drafts.**')")
            .defaultOrdering([{ field: '_updatedAt', direction: 'desc' }])
        ),

      S.listItem()
        .title('Settings')
        .icon(SlSettings)
        .child(
          S.editor()
            .id('siteSettings')
            .schemaType('siteSettings')
            .documentId('siteSettings')
        ),

      S.listItem()
        .title('Documentation')
        .icon(SlBookOpen)
        .child(
          S.documentTypeList('documentation')
            .defaultOrdering([{ field: 'title', direction: 'asc' }])
            .title('Documentation')
            .child(documentId =>
              S.document()
                .documentId(documentId)
                .schemaType('documentation')
                .views([S.view.component(HtmlPreview).title('View'), S.view.form()])
            )
        ),

      // This returns an array of all the document types
      // defined in schema.js. We filter out those that we have
      // defined the structure above
      //...S.documentTypeListItems().filter(hiddenDocTypes)
    ])
