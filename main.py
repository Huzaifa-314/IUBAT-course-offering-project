from bs4 import BeautifulSoup
from selenium import webdriver
driver = webdriver.Firefox()
driver.get('http://newsblog.rf.gd/iubat')

html = driver.page_source
soup = BeautifulSoup(html,features="lxml")

fullTable = soup.find('table', class_ ="draggable sortable bottomBorder dataTable")

print(fullTable)
