export default {
  name: 'page',
  title: 'Page',
  type: 'document',
  fields: [
    {
      name: 'title',
      title: 'Title',
      type: 'string'
    },
    {
      name: 'slug',
      title: 'Slug',
      type: 'slug',
      options: {
        source: 'title',
        maxLength: 96
      }
    },
    {
      name: 'mainImage',
      title: 'Main image',
      type: 'image',
      options: {
        hotspot: true
      }
    },
    {
      name: 'excerpt',
      type: 'excerptPortableText',
      title: 'Excerpt',
      description:
        'Wird als Überschrift in der ersten Sektion auf der Hauptseite der Page angezeigt'
    },
    {
      name: 'body',
      type: 'bodyPortableText',
      title: 'Body'
    },
    {
      title: "Sections",
      name: "sections",
      type: 'array',
      of: [
        {
          type: "section",
        }
      ],
      options:{
        // sortable: false,
         //editModal: 'popover'
        // layout: 'grid'
      }
    },
    {
      title: "Footer",
      name: "footer",
      type: 'array',
      of: [
        {
          type: "section",
        }
      ],
      options:{
        // sortable: false,
         //editModal: 'popover'
        // layout: 'grid'
      }
    }
  ]
}
