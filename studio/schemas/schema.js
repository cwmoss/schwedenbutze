// document schemas
import author from './documents/author'
import category from './documents/category'
import post from './documents/post'
import page from './documents/page'
import info from './documents/info'
import reservation from './documents/reservation'
import siteSettings from './documents/siteSettings'
import documentation from './documents/documentation'

// Object types
import bodyPortableText from './objects/bodyPortableText'
import bioPortableText from './objects/bioPortableText'
import excerptPortableText from './objects/excerptPortableText'
import mainImage from './objects/mainImage'
import authorReference from './objects/authorReference'
import section from './objects/section'
import sectionlink from './objects/sectionlink'
import infobox from './objects/infobox'
import infoboxlink from './objects/infoboxlink'

// Then we give our schema to the builder and provide the result to Sanity
export default [
  // The following are document types which will appear
  // in the studio.
  siteSettings,
  post,
  page,
  info,
  documentation,
  reservation,
  category,
  author,
  mainImage,
  section,
  sectionlink,
  infobox,
  infoboxlink,
  authorReference,
  bodyPortableText,
  bioPortableText,
  excerptPortableText

  // When added to this list, object types can be used as
  // { type: 'typename' } in other document schemas
]
