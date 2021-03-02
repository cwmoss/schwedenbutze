import S from '@sanity/desk-tool/structure-builder'

import { MdSettings } from 'react-icons/md'
import { MdPerson } from 'react-icons/md'
import { FaPencil } from "react-icons/fa"

import { HtmlPreview } from './htmlpreview'

const hiddenDocTypes = listItem =>
  !['category', 'author', 'post', 'siteSettings', 'reservation', 'documentation'].includes(listItem.getId())



export default () =>
  S.list()
    .title('Content')
    .items([
      
      S.listItem()
        .title('Inhalte')
        .schemaType('post')
        .child(S.documentTypeList('post').title('Blog posts')),

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
                .params({from: new Date().toISOString()})
            ),
            S.listItem()
            .title('Past')
            .child(
              S.documentList()
                .title('Past Reservations')
                .filter(`_type == "reservation" && departure < $today`)
                .params({today: new Date().toISOString()})
            ),
            S.documentTypeListItem('reservation').title('All Reservations')
          ])
      ),

      S.listItem()
        .title('Authors')
        .icon(MdPerson)
        .schemaType('author')
        .child(S.documentTypeList('author').title('Authors')),

      S.listItem()
        .title('Categories')
        .schemaType('category')
        .child(S.documentTypeList('category').title('Categories')),

      S.listItem()
        .title("Entwürfe")
        .icon(FaPencil)
        .child(
          S.documentList()
            .title("Entwürfe")
            .filter("_id in path('drafts.**')")
            .defaultOrdering([{ field: "_updatedAt", direction: "desc" }])
        ),

      S.listItem()
        .title('Settings')
        .icon(MdSettings)
        .child(
          S.editor()
            .id('siteSettings')
            .schemaType('siteSettings')
            .documentId('siteSettings')
        ),

      S.listItem()
        .title('Doku')
        .child(

          S.documentTypeList('documentation')
        .title('Doks')
        .child(documentId =>
          S.document()
            .documentId(documentId)
            .schemaType('documentation')
            .views([
              S.view
                .component(HtmlPreview)
                .title('Ansicht'),

              S.view.form()
              
            ])
          )
        ),
      

      // This returns an array of all the document types
      // defined in schema.js. We filter out those that we have
      // defined the structure above
      ...S.documentTypeListItems().filter(hiddenDocTypes)
    ])
